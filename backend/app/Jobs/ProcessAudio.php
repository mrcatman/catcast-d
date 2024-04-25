<?php

namespace App\Jobs;

use App\Models\Media;
use FFMpeg\FFMpeg;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessAudio extends ProcessMedia {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $type = 'audio';
    protected $allowed_codecs = ['mp3'];
    protected $file_extension = 'mp3';

    public function __construct(Media $media, $path_to_uploaded_file) {
        parent::__construct($media, $path_to_uploaded_file);
        $this->storage_folder = 'audio/'.$this->media->uuid;
    }

    protected function convert($quality = null) {
        $format = new \FFMpeg\Format\Audio\Mp3();
        $format->on('progress', function ($audio, $format, $percentage) {
            //echo "$percentage % transcoded";
        });

        $ffmpeg = FFMpeg::create($this->getFFMpegConfig());

        $audio = $ffmpeg->open($this->path_to_uploaded_file);
        $storage_paths = $this->getStoragePaths($quality);

        if ($quality) {
            $format->setAudioChannels(2)->setAudioKiloBitrate($quality);
        }
        $audio->save($format, $storage_paths['full_path']);
        $this->saveFileToDB($quality, $storage_paths);
    }

    protected function getQualityForDefaultFile() {
        return $this->streams->first()->get('bitrate');
    }

    protected function filterStreams($streams) {
        return $streams->audios();
    }

    public function afterConvert() {
        SetAudioMetadata::dispatch($this->media);
    }


}

