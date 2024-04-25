<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use App\Models\Logo;
use App\Models\Picture;


class LogosController extends Controller {


    public function getForChannel($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['edit_info']);
        $user = auth()->user();
        $logos = Logo::where(['channel_id' => $channel_id])->orderBy('id','desc')->get();
        if ($channel->pictures_data['logo']) {
             $channel_logo = $channel->pictures_data['logo'];
             if ($channel_logo && $channel_logo->id > 0) {
                 $already_in_list = count($logos->filter(function ($logo) use ($channel_logo) {
                     return  $logo->picture && $logo->picture->id == $channel_logo->id;
                 })) > 0;
                 if (!$already_in_list) {
                     $logos->prepend((object)[
                         'id' => -1,
                         'channel_id' => $channel_id,
                         'user_id' => $user->id,
                         'is_active' => false,
                         'picture_id' => $channel_logo->id,
                         'position' => null,
                         'is_channel_logo' => true,
                         'picture' => $channel_logo
                     ]);
                 }
            }
            return $logos;
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function store($channel_id) {
        PermissionsHelper::check(['edit_info'], $channel_id);
        $picture = Picture::findOrFail(request()->input('picture_id'));
        $user = auth()->user();
        if ($picture->user_id != $user->id) {
            return CommonResponses::unauthorized();
        }
        $logo = new Logo([
            'channel_id' => $channel_id,
            'user_id' => $user->id,
            'is_active' => false,
            'picture_id' => $picture->id
        ]);
        $logo->save();
        return $logo;
    }

    public function destroy($channel_id, $id) {
        $logo = Logo::findOrFail($id);

        PermissionsHelper::check(['edit_info'], $logo->channel);
        if ($logo->picture) {
            $logo->picture->deleteFile();
            $logo->picture->delete();
        }
        $logo->delete();
        return $logo;
    }

    public function set($channel_id) {
        PermissionsHelper::check(['edit_info'], $channel_id);
        $channel = Channel::findOrFail($channel_id);
        if (request()->input('unset')) {
            Logo::where(['channel_id' => $channel->id])->update(['is_active' => false]);
            return [
                'status' => 1,
            ];
        } else {
            $position = [
                'x' => (float)request()->input('position.x', 0),
                'y' => (float)request()->input('position.y', 0),
                'width' => (float)request()->input('position.width', 0)
            ];
            if (request()->input('is_channel_logo')) {
                if (isset($channel->pictures_data['logo']) && !is_array($channel->pictures_data['logo'])) {
                    $channel_logo = $channel->pictures_data['logo'];
                    $logos = Logo::where(['channel_id' => $channel_id])->orderBy('id', 'desc')->get();
                    $already_in_list = count($logos->filter(function ($logo) use ($channel_logo) {
                            return $channel_logo && $logo->picture && $logo->picture->id == $channel_logo->id;
                        })) > 0;
                    if (!$already_in_list) {
                        $logo = new Logo([
                            'channel_id' => $channel_id,
                            'user_id' => auth()->user()->id,
                            'is_active' => true,
                            'picture_id' => $channel->pictures_data['logo']->id,
                            'position' => $position
                        ]);
                        $logo->save();
                        return $logo;
                    }
                }
            } else {
                $logo = Logo::findOrFail(request()->input('id'));
                if ($logo->channel_id != $channel->id) {
                    return CommonResponses::unauthorized();
                }
                Logo::where(['channel_id' => $channel->id])->update(['is_active' => false]);
                $logo->is_active = true;
                $logo->position = $position;
                $logo->save();
                return $logo;
            }
        }
    }

    public function getActive($channel_id) {
        $logo = Logo::where(['channel_id' => $channel_id, 'is_active' => true])->first();
        return ['logo' => $logo];
    }

}
