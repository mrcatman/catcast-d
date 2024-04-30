<?php

namespace App\Jobs;

use App\Models\Media;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVideo extends ProcessMedia {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type = 'video';
    protected $allowed_codecs = ['h264', 'h265'];
    protected $file_extension = 'mp4';
    protected $dimensions;

    public function __construct(Media $media, $path_to_uploaded_file) {
        parent::__construct($media, $path_to_uploaded_file);
        $this->storage_folder = 'videos/'.$this->media->uuid;
    }

    protected function convert($quality = null) {
        $format = new \FFMpeg\Format\Video\X264();
        $format->on('progress', function ($video, $format, $percentage) {
            //echo "$percentage % transcoded";
        });

        $ffmpeg = MediaHelper::createFFMpeg;

        $video = $ffmpeg->open($this->path_to_uploaded_file);
        $storage_paths = $this->getStoragePaths($quality);

        if ($quality) {
            $aspect_ratio = $this->dimensions->getWidth() / $this->dimensions->getHeight();
            $width = $quality * $aspect_ratio;
            $video
                ->filters()
                ->resize(new Dimension($width, $quality))
                ->synchronize();
        }
        $video->save($format, $storage_paths['full_path']);
        $this->saveFileToDB($quality, $storage_paths);
    }

    protected function getQualityForDefaultFile() {
        return $this->dimensions->getHeight();
    }

    protected function afterSetStreams() {
        $this->dimensions = $this->streams->first()->getDimensions();
    }

    protected function filterStreams($streams) {
        return $streams->videos();
    }

    protected function afterConvert() {
        SetVideoMetadata::dispatch($this->media);
    }
}
