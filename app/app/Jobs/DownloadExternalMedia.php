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
use YoutubeDl\Options;
use YoutubeDl\YoutubeDl;

class DownloadExternalMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;
    protected $url;

    public function __construct(Media $video, $url) {
        $this->video = $video;
        $this->url = $url;
    }

    protected function error($error = 'dashboard.media.errors.wrong_format') {
        broadcast(new MediaConvertFailEvent($this->video, $error));
    }


    public function handle() {
        $video_filename = 'temp-ytdl-'.$this->video->id.'.mp4';
        $yt = new YoutubeDl();
        $collection = $yt->download(
            Options::create()
                ->downloadPath(storage_path('temp-uploads'))
                ->format('bestvideo[ext=mp4]+bestaudio[ext=m4a]/mp4')
                ->output($video_filename)
                ->url($this->url)
        );
        $video = $collection->getVideos()[0];

        if ($video->getError() != null) {
            broadcast(new MediaConvertFailEvent($this->video, $video->getError()));
            return;
        }
        ProcessVideo::dispatch($this->video, storage_path('temp-uploads/'.$video_filename));
    }
}
