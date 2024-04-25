<?php

Route::any('/tus/{any?}', function () {
    $server = app('tus-server');
    $server->middleware()->add(\App\Http\Middleware\TusRequestMiddleware::class);
    $server->event()->addListener('tus-server.upload.complete', function (\TusPhp\Events\TusEvent $event) {
        $details = $event->getFile()->details();
        try {
            $media_id = $details['metadata']['id'];
            $key = $details['metadata']['upload_key'];
            $upload_key = \App\Models\MediaUploadKey::where(['key' => $key, 'media_id' => $media_id])->firstOrFail();
            $media = \App\Models\Media::findOrFail($media_id);
            $classes = [
                \App\Models\Media::TYPE_VIDEO => \App\Jobs\ProcessVideo::class,
                \App\Models\Media::TYPE_AUDIO => \App\Jobs\ProcessAudio::class
            ];
            $classes[$media->media_type]::dispatch($media, $details['file_path']);
            $upload_key->delete();
        } catch (\Exception $e) {
            unlink($details['file_path']);
        }
    });
    return $server->serve();
});

