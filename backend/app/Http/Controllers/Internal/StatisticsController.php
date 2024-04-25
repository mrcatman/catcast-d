<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;

use App\Models\MediaFile;
use App\Helpers\StatisticsHelper;
use Illuminate\Support\Facades\Storage;

class StatisticsController extends Controller {

    public function increment() {
        $ip = request()->input('ip');
        $url = request()->input('url');
        $base_url = parse_url(Storage::disk('media')->url(''))['path'];
        $url_in_db = str_replace($base_url, '', $url);

        $media_file = MediaFile::where(['url' => $url_in_db])->firstOrFail();
        $media = $media_file->media;
        StatisticsHelper::increment($media, $ip);
    }

}
