<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Models\MediaUploadKey;

class TusController extends Controller
{

    public function handleWebhooks() {
        file_put_contents(storage_path('log.txt'), json_encode(request()->all()) . PHP_EOL, FILE_APPEND | LOCK_EX);

        switch (request()->input('Type')) {
            case 'pre-create':
                return $this->preCreate();
            case 'post-receive':
                return $this->postReceive();
            default:
                break;
        }
        return [
            'StopUpload' => false,
        ];
    }

    /**
     * Check if the provided upload key exists, abort upload if not
     */
    private function preCreate() {
        $upload_key = MediaUploadKey::where(['key' => request()->input('Event.Upload.MetaData.upload_key'), 'media_id' => request()->input('Event.Upload.MetaData.id')])->first();
        if (!$upload_key) {
            return [
                'StopUpload' => true,
                'HTTPResponse' => [
                    'StatusCode' => 404,
                ]
            ];
        }
        return [
            'StopUpload' => false,
        ];
    }

    /**
     * Process the uploaded file
     */
    private function postReceive()
    {
        $file_path = request()->input('Event.Upload.Storage.Path');
        try {
            $upload_key = MediaUploadKey::where(['key' => request()->input('Event.Upload.MetaData.upload_key'), 'media_id' => request()->input('Event.Upload.MetaData.id')])->firstOrFail();
            $media = $upload_key->media;
            $classes = [
                \App\Models\Media::TYPE_VIDEO => \App\Jobs\ProcessVideo::class,
                \App\Models\Media::TYPE_AUDIO => \App\Jobs\ProcessAudio::class
            ];
            $classes[$media->media_type]::dispatch($media, $file_path);
            $upload_key->delete();
            return [
                'StopUpload' => false,
            ];
        } catch (\Exception $e) {
            unlink($file_path);
            return [
                'StopUpload' => true,
                'HTTPResponse' => [
                    'StatusCode' => 404,
                ]
            ];
        }
    }

}
