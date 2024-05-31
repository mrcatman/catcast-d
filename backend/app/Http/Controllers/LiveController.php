<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Channel;
use App\Models\Media;
use App\Models\MediaFile;
use App\Helpers\StatisticsHelper;

class LiveController extends Controller {

    public function stream($id) {
        $channel = Channel::findOrFail($id); // todo: cache?
        $playlist_file_path = public_path('live/'.$id.'/index.m3u8');
        if (!file_exists($playlist_file_path)) {
            abort(404);
        }
        $contents = file_get_contents($playlist_file_path); // todo: add passwords, etc.
        $contents = explode(PHP_EOL, $contents);

        $contents = array_map(function($line) use ($id) {
            if (str_ends_with($line, '.ts')) {
                return '/live/'.$id.'/'.$line;
            }
            return $line;
        }, $contents);

        StatisticsHelper::increment($channel->activeBroadcast);
        return implode(PHP_EOL, $contents);
    }

}
