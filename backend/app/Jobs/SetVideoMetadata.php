<?php

namespace App\Jobs;

use App\Enums\PrivacyStatuses;
use App\Events\Media\MediaUpdatedEvent;
use App\Helpers\MediaHelper;
use App\Models\Picture;
use App\Models\Media;
use App\Models\MediaFile;
use App\Models\VideoGridThumbnail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SetVideoMetadata implements ShouldQueue {

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $video;

    public function __construct(Media $video) {
        $this->video = $video;
    }


    public function handle() {
        if (count($this->video->files) === 0) {
            return;
        }
        $file = $this->video->files->first();
        if ($file->type !== MediaFile::TYPE_LOCAL) {
            return;
        }

        $path = Storage::disk('media')->path($file->url);
        $base_path = implode('/',array_slice(explode('/', $file->url),0, count(explode('/', $file->url)) - 1));
        $thumbnail_url = $base_path.'/thumbnail.jpg';
        $thumbnail_path = Storage::disk('media')->path($thumbnail_url);

        $thumbnail_created = MediaHelper::generateThumbnail($path, $thumbnail_path, floor($this->video->duration / 2));
        if ($thumbnail_created) {
            $picture = Picture::firstOrCreate([
                'user_id' => $this->video->user_id,
                'domain' => null,
                'relative_url' => $thumbnail_url,
                'disk' => 'media'
            ]);
            $this->video->thumbnail_id = $picture->id;
            $this->video->save();
        }

        $grid_thumbnail_url = $base_path.'/thumbnail.grid.jpg';
        $grid_thumbnail_path = Storage::disk('media')->path($grid_thumbnail_url);
        $grid_thumbnail_data = MediaHelper::generateGridThumbnail($path, $grid_thumbnail_path, $this->video->duration);
        if ($grid_thumbnail_data) {
            $grid_picture = Picture::firstOrCreate([
                'user_id' => $this->video->user_id,
                'domain' => null,
                'relative_url' => $grid_thumbnail_url,
                'disk' => 'media'
            ]);
            $video_grid_thumbnail = VideoGridThumbnail::firstOrNew([
                'video_id' => $this->video->id,
            ]);
            $video_grid_thumbnail->fill($grid_thumbnail_data);
            $video_grid_thumbnail->picture_id = $grid_picture->id;
            $video_grid_thumbnail->save();
        }


        broadcast(new MediaUpdatedEvent($this->video));
    }
}
