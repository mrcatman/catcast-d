<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Picture extends Model {

    protected $table = 'pictures';
    protected $appends = ['full_url'];
    protected $hidden = ['disk'];
    protected $guarded = [];

    public function getDiskAttribute($disk) {
        return $disk ?: 'public';
    }

    public function getFullUrlAttribute() {
        if (!$this->domain) {
            return Storage::disk($this->disk)->url($this->relative_url);
        } else {
            return $this->domain. '/' . $this->relative_url;
        }
    }

    public function deleteFile() {
        if (!$this->domain) {
            $file_path = Storage::disk($this->disk)->path($this->relative_url);
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }



}
