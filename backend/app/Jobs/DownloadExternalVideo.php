<?php

namespace App\Jobs;

use App\Events\Media\MediaConvertFailEvent;
use App\Events\Media\MediaConvertSuccessEvent;
use App\Models\Media;
use App\Models\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;

class DownloadExternalVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;
    protected $url;

    public function __construct(Media $video, $url) {
        $this->video = $video;
        $this->url = $url;
    }

    protected function error($error = 'dashboard.videos.errors.wrong_format') {
        broadcast(new MediaConvertFailEvent($this->video, $error));

    }


    public function handle() {
        $format = 'bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4'; //todo: move to config

        $download_folder = 'videos/'.$this->video->id;
        if (!file_exists(public_path($download_folder))) {
            mkdir(public_path($download_folder));
        }
        $download_path = $download_folder.'/default.mp4';

        $url = $this->url;

        $command = 'youtube-dl -f '.$format.' -o '.public_path($download_path).' '.$url;

        $descriptorspec = [
         //   0 => ['pipe', 'r'],  // stdin
         //   1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];
        $process = proc_open($command, $descriptorspec, $pipes);
        //   $stdout = stream_get_contents($pipes[1]);
        //   fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $stderr = explode(PHP_EOL, $stderr);

        $code = proc_close($process);

        $success = $code === 0;
        if (!$success) {
            $error = count($stderr) >= 2 ? $stderr[count($stderr) - 2] : '';
            broadcast(new MediaConvertFailEvent($this->video, $error));
            File::deleteDirectory(public_path($download_folder));
            return;
        }

        $channel = $this->video->channel;
        $file_size = filesize(public_path($download_path));
        if ($channel->getOccupiedDiskSpace() + $file_size > $channel->getTotalDiskSpace()) {
            broadcast(new MediaConvertFailEvent($this->video, 'dashboard.videos.errors.not_enough_space'));
            File::deleteDirectory(public_path($download_folder));
            return;
        }

        $file = new MediaFile([ // todo: quality
            'video_id' => $this->video->id,
            'type' => MediaFile::TYPE_LOCAL,
            'file_size' => $file_size,
            'url' => $download_path,
        ]);
        $file->save();

        broadcast(new MediaConvertSuccessEvent($this->video));
        SetVideoMetadata::dispatch($this->video);
    }
}
