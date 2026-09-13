<?php

namespace App\Jobs;

use App\Models\Audio;
use App\Models\Channel;
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
class GenerateWaveform implements ShouldQueue
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


    public function handle()
    {
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
        $err = stream_get_contents($pipes[2]);
        $ret = proc_close($proc);
        dd($lines1, $lines2);
        if ($ret !== 0) {
            throw new \Exception('Failed to run `sox` command. Error:' . PHP_EOL . $err);
        }

    }
}
