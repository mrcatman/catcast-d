<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use App\Models\Picture;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Intervention\Image\Exception\NotReadableException;

class UploadController extends Controller {


    public $max_file_size = 10485760;
    public $max_width = 1600;

    public function uploadPictures()
    {
        $file = request()->file('picture');
        if ($file) {
            if ($file->getSize() >= $this->max_file_size) {
                return response()->json(['message' => 'upload.errors.file_too_big'], 422);
            }
            try {
                $picture = Image::make($file);
                if ($picture->width() > $this->max_width) {
                    $picture->resize($this->max_width, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                }
                $mime = explode("/", $picture->mime)[1];
                if ($mime == "jpeg") {
                    $mime = "jpg";
                }
                $id = (request()->has('id') && request()->input('id') != -1) ? request()->input('id') : auth()->user()->id;
                $filename = $id . "-" . uniqid() . "." . $mime;
                $folder = "uploads/" . date("dmY");
                if (request()->has('folder')) {
                    $folder .= "/" . request()->input('folder');
                }
                $relative_url = $folder . "/" . $filename;

                if (!file_exists(Storage::disk('public')->path($folder))) {
                    mkdir(Storage::disk('public')->path($folder), 0755, true);
                }
                $picture->save(Storage::disk('public')->path($relative_url), 75);
                $picture_item = new Picture([
                    'user_id' => auth()->user()->id,
                    'domain' => null,
                    'relative_url' => $relative_url,
                ]);
                if (request()->has('channel_id')) {
                    $channel = Channel::findOrFail(request()->input('channel_id'));
                    if (PermissionsHelper::hasAny($channel)) {
                        $picture_item->channel_id = $channel->id;
                    }
                }
                $picture_item->save();
                return $picture_item;
            } catch (\Exception $e) {
                return response()->json(['message' => 'upload.errors.format_wrong', 'error' => $e->getMessage()], 422);
            }
        } else {
            return response()->json(['message' => 'upload.errors.no_file_input'], 422);
        }
    }

}
