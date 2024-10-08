<?php

namespace App\Jobs;

use App\Enums\PrivacyStatuses;
use App\Events\Media\MediaConvertFailEvent;
use App\Events\Media\MediaConvertSuccessEvent;
use App\Helpers\MediaHelper;
use App\Models\Media;
use App\Models\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Hidehalo\Nanoid\Client as NanoidClient;

class ProcessMedia implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $media;
    protected $path_to_uploaded_file;
    protected $streams;

    protected $allowed_codecs;
    protected $storage_folder;
    protected $ready_files = [];

    protected $file_extension;

    public $timeout = 60 * 60 * 24;

    public function __construct(Media $media, $path_to_uploaded_file) {
        $this->media = $media;
        $this->path_to_uploaded_file = $path_to_uploaded_file;
    }

    protected function getStoragePaths($quality = 'default') {
        $uuid = (new NanoidClient())->generateId();
        $path = $this->storage_folder.'/'.$uuid.'.'.$quality.'.'.$this->file_extension;
        $full_path = Storage::disk('media')->path($path);
        return [
            'path' => $path,
            'full_path' => $full_path
        ];
    }

    protected function removeFilesAndSendError($error = 'dashboard.media.errors.wrong_format') {
        broadcast(new MediaConvertFailEvent($this->media, $error));
        if (File::exists($this->path_to_uploaded_file)) {
            File::delete($this->path_to_uploaded_file);
        }
        if (File::exists(Storage::disk('media')->path($this->storage_folder))) {
            File::deleteDirectory(Storage::disk('media')->path($this->storage_folder));
        }
    }

    protected function convert($quality = null) {

    }

    protected function saveFileToDB($quality, $storage_paths) {
        $file_size = File::size($storage_paths['full_path']);
        // todo: add dimensions
        $file = new MediaFile([
            'media_id' => $this->media->id,
            'type' => MediaFile::TYPE_LOCAL,
            'file_size' => $file_size,
            'url' => $storage_paths['path'],
            'quality' => $quality ?: $this->getQualityForDefaultFile(),
            'original' => !$quality
        ]);
        $file->save();
        $this->ready_files[] = $file;
    }

    protected function getTotalFilesSize() {
        $total_files_size = 0;
        foreach ($this->ready_files as $file) {
            $total_files_size+= $file->file_size;
        }
        return $total_files_size;
    }

    public function handle() {
        $channel = $this->media->channel;
        if (!file_exists($this->path_to_uploaded_file)) {
            $this->removeFilesAndSendError();
            return;
        }
        $file_size = filesize($this->path_to_uploaded_file);
        if ($channel->getOccupiedDiskSpace() + $file_size > $channel->getTotalDiskSpace()) {
            $this->removeFilesAndSendError('dashboard.media.errors.not_enough_space');
            return;
        }

        $ffprobe = MediaHelper::createFFProbe();
        if (!$ffprobe->isValid($this->path_to_uploaded_file)) {
            $this->removeFilesAndSendError();
            return;
        }

        $streams = $this->filterStreams($ffprobe->streams($this->path_to_uploaded_file));
        if (count($streams) === 0) {
            $this->removeFilesAndSendError();
            return;
        }
        $this->streams = $streams;
        $this->afterSetStreams();

        $format = $ffprobe->format($this->path_to_uploaded_file);
        $duration = (float)$format->get('duration');
        if ($duration <= 0) {
            $this->removeFilesAndSendError();
            return;
        }

        if (!File::exists(Storage::disk('media')->path($this->storage_folder))) {
            File::makeDirectory(Storage::disk('media')->path($this->storage_folder), 0755, true, true);
        }

        $codec = $streams->first()->get('codec_name');
        $reencode_original_file = config('site.media.'.$this->type.'.reencode_original_file', false);
        $store_original_file = config('site.media.'.$this->type.'.store_original_file', false);

        $qualities = config('site.media.' . $this->type . '.encode_qualities', []);

        if ($store_original_file) {
            $should_reencode_original_file = !in_array($codec, $this->allowed_codecs)
                || $reencode_original_file
                || $this->media->source_type === Media::SOURCE_TYPE_RECORD;

            if ($should_reencode_original_file) {
                $qualities[] = null;
            } else {
                $storage_paths = $this->getStoragePaths();
                File::move($this->path_to_uploaded_file, $storage_paths['full_path']);
                $this->saveFileToDB(null, $storage_paths);
            }
        }

        $default_file_quality = $this->getQualityForDefaultFile();

        foreach ($qualities as $quality) {
            if ($quality < $default_file_quality) {
                try {
                    $this->convert($quality);
                } catch (\Exception $e) {}
            }
        }

        if (!$store_original_file) {
            File::delete($this->path_to_uploaded_file);
        }

        if (count($this->ready_files) === 0) {
            $this->removeFilesAndSendError();
            return;
        }

        if ($channel->getOccupiedDiskSpace() + $this->getTotalFilesSize() > $channel->getTotalDiskSpace()) {
            $this->removeFilesAndSendError('dashboard.media.errors.not_enough_space');
            return;
        }

        $this->media->duration = $duration;

        $folder = $this->media->folder;
        if (!$folder || $folder->is_public || ($this->media->source_type == Media::SOURCE_TYPE_RECORD && $channel->additional_settings['recording']['records_public'])) {
            $this->media->privacy_status = PrivacyStatuses::PUBLIC;
        }
        $this->media->save();
        $this->afterConvert();
        broadcast(new MediaConvertSuccessEvent($this->media));
    }

    protected function filterStreams($streams) {
        return $streams;
    }

    protected function afterSetStreams() {

    }

    protected function afterConvert() {

    }

    protected function getQualityForDefaultFile()
    {
        return 0;
    }
}
