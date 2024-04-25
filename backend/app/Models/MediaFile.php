<?php

namespace App\Models;

use App\Helpers\ConfigHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MediaFile extends Model {

    protected $table = 'media_files';

    protected $guarded = [];
    protected $appends = ['quality_name', 'playback_url', 'download_url', 'mime_type'];
    protected $hidden = ['media'];

    const TYPE_LOCAL = 'TYPE_LOCAL'; // todo: change

    public function media() {
        return $this->belongsTo(Media::class);
    }

    public static function boot() {
        parent::boot();

        static::deleting(function($video_file) {
            if ($video_file->type === self::TYPE_LOCAL && $video_file->url != '') {
                $folder = dirname(public_path($video_file->url));
                if (File::exists($folder)) {
                    File::deleteDirectory($folder);
                }
            }
        });
    }

    public function getQualityNameAttribute() {
        if ($this->quality) {
            if ($this->media->media_type === Media::TYPE_VIDEO) {
                return $this->quality.'p';
            } elseif ($this->media->media_type === Media::TYPE_AUDIO) {
                return $this->quality.' kb/s';
            }
        }
        return null;
    }

    public function getPlaybackUrlAttribute() {
        if ($this->type === MediaFile::TYPE_LOCAL) {
            if ($this->media->media_type === Media::TYPE_VIDEO && ConfigHelper::enableHLS()) {
                return $this->hls_url;
            } elseif ($this->media->media_type === Media::TYPE_AUDIO || !ConfigHelper::enableHLS()) {
                return $this->download_url;
            }
        }
        return $this->url;
    }

    public function getDownloadUrlAttribute() {
        return Storage::disk('media')->url($this->url);
    }

    public function getHlsUrlAttribute() {
        $hls_url = str_replace('videos/', 'videos-hls/', $this->url);
        $hls_url = str_replace('.mp4', '/index.m3u8', $hls_url);
        return ConfigHelper::siteURL().'/'.$hls_url;
    }

    public function getStoragePathAttribute() {
        if ($this->type === MediaFile::TYPE_LOCAL) {
            return Storage::disk('media')->path($this->url);
        }
        return null;
    }

    public function getMimeTypeAttribute() {
        if ($this->media->media_type === Media::TYPE_VIDEO) {
            return 'video/mp4';
        } elseif ($this->media->media_type === Media::TYPE_AUDIO) {
            return 'audio/mpeg';
        }
        return null;
    }

}
