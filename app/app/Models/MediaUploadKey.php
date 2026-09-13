<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Authorises one upload of one media item to one streaming server.
 *
 * The streaming servers hold no user table: tusd asks the application whether
 * an upload may start (TusController::preCreate), and this row is the whole of
 * the answer. It is therefore scoped as narrowly as it can be -- to a media
 * item, to a server, to a size, and to a window.
 */
class MediaUploadKey extends Model {

    protected $table = 'media_upload_keys';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    // How long a key has to *start* an upload. Expiry is deliberately not
    // rechecked once the transfer is under way: a large file can take hours,
    // and it was authorised when it began.
    const LIFETIME_MINUTES = 60;

    public function media() {
        return $this->belongsTo(Media::class);
    }

    /**
     * @param Media $media Media the upload belongs to
     * @param string $server_id Streaming server the upload must go to
     * @param int|null $max_size Largest upload allowed, in bytes
     * @return MediaUploadKey
     */
    public static function issue(Media $media, $server_id, $max_size = null) {
        $key = new self([
            'media_id' => $media->id,
            'server_id' => $server_id,
            'key' => Str::random(48),
            'max_size' => $max_size,
            'expires_at' => Carbon::now()->addMinutes(self::LIFETIME_MINUTES),
        ]);
        $key->save();
        return $key;
    }

    /**
     * The key matching a tus hook's metadata, or null. Looked up by key and
     * media together, so a valid key cannot be pointed at someone else's media.
     * @param string|null $key
     * @param mixed $media_id
     * @return MediaUploadKey|null
     */
    public static function lookup($key, $media_id) {
        if (!is_string($key) || $key === '' || !$media_id) {
            return null;
        }
        return self::where(['key' => $key, 'media_id' => $media_id])->first();
    }

    public function getIsExpiredAttribute() {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsUsedAttribute() {
        return $this->used_at !== null;
    }

    /**
     * May this key still open an upload on the server that is asking?
     * @param string|null $server_id Server the request was recognised as
     * @param int $size Declared upload length, in bytes
     * @return bool
     */
    public function canStartUpload($server_id, $size) {
        return !$this->is_used
            && !$this->is_expired
            && $this->server_id === $server_id
            && $size > 0
            && ($this->max_size === null || $size <= $this->max_size);
    }

    public function markUsed() {
        $this->used_at = Carbon::now();
        $this->save();
    }

}
