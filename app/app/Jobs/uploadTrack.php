<?php

namespace App\Jobs;

use App\Models\Audio;
use App\Models\Channel;
use App\Models\Folder;
use App\Notifications\ChannelNewRecording;
use App\Notifications\playlistNewRecording;
use App\Models\RadioServer;
use App\Helpers\RadioAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use PhpId3\Id3TagsReader;
class uploadTrack implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $track;
    private $radio_api;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Audio $track)
    {
        $this->track = $track;
        $this->radio_api = new RadioAPI();
    }

    private function _translit($string) {
       $converter = array(
                'а' => 'a',   'б' => 'b',   'в' => 'v',
                'г' => 'g',   'д' => 'd',   'е' => 'e',
                'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
                'и' => 'i',   'й' => 'y',   'к' => 'k',
                'л' => 'l',   'м' => 'm',   'н' => 'n',
                'о' => 'o',   'п' => 'p',   'р' => 'r',
                'с' => 's',   'т' => 't',   'у' => 'u',
                'ф' => 'f',   'х' => 'h',   'ц' => 'c',
                'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
                'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
                'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

                'А' => 'A',   'Б' => 'B',   'В' => 'V',
                'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
                'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
                'И' => 'I',   'Й' => 'Y',   'К' => 'K',
                'Л' => 'L',   'М' => 'M',   'Н' => 'N',
                'О' => 'O',   'П' => 'P',   'Р' => 'R',
                'С' => 'S',   'Т' => 'T',   'У' => 'U',
                'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
                'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
                'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
                'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
       );
       $transliterated = strtr($string, $converter);
       return $transliterated;
    }

    private function _sanitizeFileName($filename) {
            $str = strip_tags($filename);
            $str = $this->_translit($filename);
            $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
            $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
            $str = strtolower($str);
            $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
            $str = htmlentities($str, ENT_QUOTES, "utf-8");
            $str = preg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
            $str = str_replace(' ', '_', $str);
            $str = rawurlencode($str);
            $str = str_replace('%', '-', $str);
            return $str;
    }

    public function _getLength($track) {
        $path = public_path()."/uploads_temp/tmp_".$track->id.".mp3";
        $command = "ffmpeg -i ".$path.' 2>&1 | grep -o \'Duration: [0-9:.]*\'';
        $result = shell_exec($command);
        if (!$result) {
            return null;
        }
        $duration = str_replace('Duration: ', '', $result);
        $timeArr = explode(":",$duration);
        $hours = $timeArr[0];
        $minutes = $timeArr[1];
        $seconds = explode(".",$timeArr[2])[0];
        $ms = (int)explode(".",$timeArr[2])[1];
        $duration_number = (($hours*3600+$minutes*60+$seconds)*100 + $ms);
        return $duration_number;
    }

    public function handle()
    {
        try {
            $track = $this->track;
            $file = new File(public_path() . "/uploads_temp/tmp_" . $track->id . ".mp3");
            $server = $this->radio_api->getServerByChannelId($track->channel_id);
            $ftp = Storage::disk('ftp_' . $server->id);

            $folder = $server->storage_path."/" . $track->channel_id . "/";

            $filename = $this->_sanitizeFileName($track->filename);
            $ftp->putFileAs($folder, $file, $filename);

            $found_id3 = false;
            $track->title = $track->filename;

            ini_set('memory_limit', '5000M');




        if (!$found_id3) {
            if (strpos($track->filename, ".mp3") !== false) {
                $track->title = substr($track->title, 0, strpos($track->filename, ".mp3"));
            }
            if (count($splitted = explode("-", $track->title)) === 2) {
                $track->title = trim($splitted[1]);
                $track->author = trim($splitted[0]);
            } elseif (count($splitted = explode("—", $track->title)) === 2) {
                $track->title = trim($splitted[1]);
                $track->author = trim($splitted[0]);
            }
        }

        //$channel = Channel::find($track->channel_id);
        $track->radio_server = $server->id;
        $server_path = $folder;
        if ($track->folder_id > -1) {
            //$server_path.= $track->folder_id."/";
        }
        $track->filename = $filename;
        $server_path .= $filename;
        $track->radio_server_path = $server_path;
        $track->length = $this->_getLength($track);
        if ($track->length > 0) {
            $track->upload_status = 1;
            $track->save();
            if ($track->folder_id > -1) {
                $folder = Folder::find($track->folder_id);
                if ($folder && $folder->connected_playlist_id > 0) {
                    (new playlistNewRecording($track, true))->send($folder->connected_playlist_id);
                }
                if ($folder->is_public) {
                    (new ChannelNewRecording($track))->send($track->channel_id);
                }
            }
            // $this->generateWaveform();

        } else {
            $track->upload_status = 0;
            $track->save();
        }
        $track->deleteTempFile();
        } catch (\Exception $e) {
            $track->upload_status = 0;
            $track->save();
        }
    }

    public function generateWaveform() {
        $track = $this->track;
        $filename = public_path()."/uploads_temp/tmp_".$track->id.".mp3";
        $sampleRate = null;
        $samples = null;
        $duration = null;
        $out = null;
        $ret = null;

        exec('sox  --i ' . escapeshellarg($filename) . ' 2>&1', $out, $ret);
        $str = implode('|', $out);
        $match = null;
        if (preg_match('/Channels?\s*\:\s*(\d+)/ui', $str, $match)) {
            $channels = intval($match[1]);
        }
        $match = null;
        if (preg_match('/Sample\s*Rate\s*\:\s*(\d+)/ui', $str, $match)) {
            $sampleRate = intval($match[1]);
        }
        $match = null;
        if (preg_match('/Duration.*[^\d](\d+)\s*samples?/ui', $str, $match)) {
            $samples = intval($match[1]);
        }
        if ($samples && $sampleRate) {
            //$duration = 1.0 * $samples / $sampleRate;
        }
        if ($ret !== 0) {
            throw new \Exception('Failed to get audio info.' . PHP_EOL . 'Error: ' . implode(PHP_EOL, $out) . PHP_EOL);
        }
        $width = 12;
        $linesPerPixel= 8;
        $samplesPerLine = 512;
        $needChannels = 1;
        $samplesPerPixel = $samplesPerLine * $linesPerPixel;
        $needRate = 1.0 * $width * $samplesPerPixel * $sampleRate / $samples;

        $command = 'sox ' . escapeshellarg($filename) .
            ' -c ' . $needChannels .
            ' -r ' . $needRate . ' -e floating-point -t raw -';
        $outputs = [
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];
        $pipes = null;
        $proc = proc_open($command, $outputs, $pipes);
        if (!$proc) {
            throw new \Exception('Failed to run `sox` command');
        }
        $lines1 = [];
        $lines2 = [];
        while ($chunk = fread($pipes[1], 4 * $needChannels * $samplesPerLine)) {
            $data = unpack('f*', $chunk);
            $channel1 = [];
            foreach ($data as $index => $sample) {
                $channel1 []= $sample;
            }
            $lines1 []= abs(round(min($channel1), 2));
            $lines2 []= round(max($channel1), 2);
        }
        $track->waveform_data = [$lines1, $lines2];
        $track->save();
    }
}
