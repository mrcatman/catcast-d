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
            file_put_contents(storage_path('log.txt'), 'Searching key: '.request()->input('Event.Upload.MetaData.upload_key') . PHP_EOL, FILE_APPEND | LOCK_EX);
            $upload_key = MediaUploadKey::where(['key' => request()->input('Event.Upload.MetaData.upload_key'), 'media_id' => request()->input('Event.Upload.MetaData.id')])->firstOrFail();
            $media = $upload_key->media;
            file_put_contents(storage_path('log.txt'), 'Found key: '.request()->input('Event.Upload.MetaData.upload_key') . PHP_EOL, FILE_APPEND | LOCK_EX);
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
            file_put_contents(storage_path('log.txt'), $e->getMessage() . PHP_EOL, FILE_APPEND | LOCK_EX);
            file_exists($file_path) && unlink($file_path);
            return [
                'StopUpload' => true,
                'HTTPResponse' => [
                    'StatusCode' => 404,
                ]
            ];
        }
    }

}
