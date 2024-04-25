<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;

use App\Models\Media;
use App\Models\MediaFile;
use App\Helpers\StatisticsHelper;

class MediaController extends Controller {


    public function generateVODResponse($uuid, $quality) {
        $media = Media::where(['uuid' => $uuid])->firstOrFail();
        $file = MediaFile::where(['media_id' => $media->id, 'url' => 'videos/'.$uuid.'/'.$quality.'.mp4'])->firstOrFail();
        $path = $file->storage_path;

        StatisticsHelper::increment($media);

        return response()->json([
            'sequences' => [[
                'clips' => [[
                    'type' => 'source',
                    'path' => $path
                ]]
            ]]
        ]);
    }

}
