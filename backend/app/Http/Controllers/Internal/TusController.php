<?php

namespace App\Http\Controllers\Internal;

use App\Helpers\ServersHelper;
use App\Http\Controllers\Controller;
use App\Models\MediaUploadKey;

class TusController extends Controller
{

    public function handleWebhooks() {
        switch (request()->input('Type')) {
            case 'pre-create':
                return $this->preCreate();
            case 'post-finish':
                return $this->postReceive();
            default:
                break;
        }
        return [
            'StopUpload' => false,
        ];
    }

    /**
     * Decide whether an upload may start. This runs before any bytes move, so
     * it is where the upload is authorised, bound to a server and capped.
     */
    private function preCreate() {
        $key = MediaUploadKey::lookup(
            request()->input('Event.Upload.MetaData.upload_key'),
            request()->input('Event.Upload.MetaData.id')
        );

        // tus can defer the length, but then there is nothing to check the
        // quota against, so an upload that will not declare its size is refused.
        if (request()->boolean('Event.Upload.SizeIsDeferred')) {
            return $this->reject(411);
        }
        $size = (int)request()->input('Event.Upload.Size');

        // ServersHelper::idFromRequest() is the server EnsureRequestIsInternal
        // recognised the caller as, not something the caller named, so a key
        // issued for one server cannot be spent on another.
        if (!$key || !$key->canStartUpload(ServersHelper::idFromRequest(), $size)) {
            return $this->reject(404);
        }

        return [
            'StopUpload' => false,
        ];
    }

    /**
     * Take delivery of a finished upload.
     */
    private function postReceive()
    {
        $file_path = request()->input('Event.Upload.Storage.Path');
        $key = MediaUploadKey::lookup(
            request()->input('Event.Upload.MetaData.upload_key'),
            request()->input('Event.Upload.MetaData.id')
        );

        if (!$key || !$key->media) {
            // Nothing to attribute the file to. It was never authorised, or the
            // media has since been deleted, so the bytes are not kept.
            file_exists($file_path) && unlink($file_path);
            return $this->stop(404);
        }

        // tusd retries this hook when it fails. The upload has already been
        // handed off, so a repeat is acknowledged rather than treated as an
        // unknown key -- which would have deleted the file underneath the job
        // that is converting it.
        if ($key->is_used) {
            return [
                'StopUpload' => false,
            ];
        }

        $classes = [
            \App\Models\Media::TYPE_VIDEO => \App\Jobs\ProcessVideo::class,
            \App\Models\Media::TYPE_AUDIO => \App\Jobs\ProcessAudio::class
        ];
        $key->markUsed();
        $classes[$key->media->media_type]::dispatch($key->media, $file_path);

        return [
            'StopUpload' => false,
        ];
    }

    /**
     * Refuse an upload before it is created. This has to be RejectUpload, not
     * StopUpload: both answer the client with the status, but StopUpload lets
     * tusd create the upload first and only then terminate it, leaving the
     * allocated files behind. Ten refused attempts left twenty files on disk.
     */
    private function reject($status) {
        return [
            'RejectUpload' => true,
            'HTTPResponse' => [
                'StatusCode' => $status,
            ]
        ];
    }

    /**
     * Terminate an upload that already exists. StopUpload is the right field
     * here -- there is nothing left to reject.
     */
    private function stop($status) {
        return [
            'StopUpload' => true,
            'HTTPResponse' => [
                'StatusCode' => $status,
            ]
        ];
    }

}
