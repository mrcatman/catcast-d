<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Like;
use App\Models\Channel;


class ChannelSubscribersController extends Controller {

    public function getList($channel_id) {
        $channel = Channel::findOrFail($channel_id);
        PermissionsHelper::checkHasAny($channel);
        $subscribers = Like::with('user:id,username')->where(['entity_type' => Channel::getEntityType(), 'entity_id' => $channel->id])->orderBy('updated_at', 'desc');
        if (request()->has('search')) {
            $subscribers = $subscribers->whereHas('user', function ($query) {
                return $query->where('username', 'LIKE', '%'.request()->input('search').'%');
            });
        }
        return $subscribers->paginate(request()->input('count', 24));
    }


}
