<?php

namespace App\Jobs;

use App\Events\Media\MediaUpdatedEvent;
use App\Models\Media;
use App\Models\MediaFile;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SetAudioMetadata implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $audio;

    public function __construct(Media $audio) {
        $this->audio = $audio;
    }


    public function handle() {
        if (count($this->audio->files) === 0) {
            return;
        }
        $file = $this->audio->files->first();
        if ($file->type !== MediaFile::TYPE_LOCAL) {
            return;
        }

        $path = Storage::disk('media')->path($file->url);

        $metadata = $this->getMetadataFromID3Tags($path);

        if (empty($metadata['title'])) {
            $file_name = explode('.mp3', basename($path))[0];
            $file_name = str_replace('_', ' ', $file_name);
            $metadata_from_file_name = explode('-', $file_name);
            if (count($metadata_from_file_name) === 1) {
                $metadata['title'] = $metadata_from_file_name;
            } else {
                $metadata['title'] = trim($metadata_from_file_name[0]);
                $metadata['artist'] = trim($metadata_from_file_name[1]);
            }
        }
        $this->audio->additional_settings = [
            'metadata' => $metadata
        ];
        if (!empty($metadata['title'])) {
            $this->audio->title = !empty($metadata['artist']) ? $metadata['artist']. ' - '.$metadata['title'] : $metadata['title'];
        }
        $this->audio->save();
        broadcast(new MediaUpdatedEvent($this->audio));
    }


    private function getMetadataFromID3Tags($path) {
        $getID3 = new \getID3();

        $tags = $getID3->analyze($path);
        $getID3->CopyTagsToComments($tags);

        $metadata = [];
        foreach (['title', 'artist', 'album'] as $tag) {
            if (isset($tags['comments_html'][$tag][0])) {
                $tag_value = $tags['comments_html'][$tag][0];
                try {
                    $tag_value = html_entity_decode($tag_value);
                    $tag_value = iconv('utf-8', 'iso-8859-15', $tag_value);
                    $tag_value = iconv('cp1251', 'utf-8', $tag_value);
                } catch (\Exception $e) {}
                $metadata[$tag] = $tag_value;
            }
        }
        return $metadata;
    }

}
