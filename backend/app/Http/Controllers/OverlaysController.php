<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Overlay;

use App\Models\Picture;


class OverlaysController extends Controller {


    public function getForChannel($channel_id) {
        PermissionsHelper::check(['live_broadcast'], $channel_id);
        $overlays = Overlay::where(['channel_id' => $channel_id])->orderBy('id','desc');
        $overlays = $overlays->where(['studio_version' => request()->input('studio_version', 1)]);
        $overlays = $overlays->get();
        return $overlays;
    }

    public function store() {
        PermissionsHelper::check(['live_broadcast']);
        if (request()->filled('type_name') && request()->filled('data')) {
            $overlay = new Overlay([
                'channel_id' =>request()->input('channel_id'),
                'user_id' => auth()->user()->id,
                'title' =>request()->input('title', 'NewOverlay'),
                'type_name' =>request()->input('type_name'),
                'data' => request()->input('data'),
                'studio_version' =>request()->input('studio_version', 1)
            ]);
            $overlay->save();
            return $overlay;
        } else {
            return response()->json(['message' => 'errors.no_parameters'], 422);
        }
    }

    public function update($id) {
        $overlay = Overlay::findOrFail($id);
        PermissionsHelper::check(['live_broadcast'], $overlay->channel, $overlay);
        if (request()->filled('type_name') && request()->filled('data')) {
            $overlay->title = request()->input('title', 'NewOverlay');
            $overlay->type_name = request()->input('type_name');
            $overlay->data = request()->input('data');
            $overlay->save();
            return $overlay;
        } else {
            return response()->json(['message' => 'errors.no_parameters'], 422);
        }
    }

    public function destroy($id) {
        $overlay = Overlay::findOrFail($id);
        PermissionsHelper::check(['live_broadcast'], $overlay->channel, $overlay);
        $overlay->delete();
        return $overlay;
    }

    public function getMediaByChannel($channel_id) {
        PermissionsHelper::check(['live_broadcast'], $channel_id);
        $pictures = Picture::where('folder', 'LIKE', '%overlays%')->where(['channel_id' => $channel_id])->get();
        return $pictures;
    }

    public function deleteMedia($id) {
        $picture = Picture::findOrFail($id);
        PermissionsHelper::check(['live_broadcast'], $picture->channel, $picture);
        $picture->delete();
        return $picture;
    }

}
