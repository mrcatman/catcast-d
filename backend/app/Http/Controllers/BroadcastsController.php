<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Broadcast;
use App\Models\BroadcastCategory;
use App\Models\Channel;

class BroadcastsController extends Controller{

    private $validate_rules = [
        'title' => 'required',
        'description' => 'sometimes',
        'category.id' => 'sometimes',
        'category.name' => 'sometimes',
        'tags' => 'sometimes|array'
    ];

    public function getByChannel($channel_id){
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['live_broadcast']);

        $broadcasts = $channel->broadcasts();
        if (request()->input('type') == 'planned') {
            $broadcasts = $broadcasts->planned();
        } elseif (request()->input('type') == 'finished') {
            $broadcasts = $broadcasts->finished();
        } else {
            $broadcasts = $broadcasts->notActive();
        }
        if (request()->has('search')) {
            $broadcasts = $broadcasts->where(function($q) {
                $q->where('title', 'LIKE', '%'.request()->input('search').'%');
                $q->orWhere('description', 'LIKE', '%'.request()->input('search').'%');
            });
        }
        $broadcasts = $broadcasts->paginate(request()->input('count', 10));

        $full_permissions = PermissionsHelper::getStatus(['live_broadcast'], $channel);
        $user = auth()->user();
        if ($user) {
            $broadcasts->getCollection()->transform(function ($broadcast) use ($user, $full_permissions) {
                $broadcast->can_delete = $full_permissions || $broadcast->user_id == $user->id;
                $broadcast->can_edit = $broadcast->can_delete && !$broadcast->ended_at;
                return $broadcast;
            });
        }
        return $broadcasts;
    }

    public function getActive($channel_id) {
        $channel = Channel::findOrFail($channel_id);
        $data = $channel->activeBroadcast ?: $channel->additional_settings['default_broadcast_metadata'];
        $data['is_online'] = !!$channel->activeBroadcast;

        if (!$channel->activeBroadcast && $data['category_id']) {
            $data['category'] = BroadcastCategory::find($data['category_id']);
        }
        return $data;
    }

    public function updateActive($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['live_broadcast']);
        return $this->fillData($channel->activeBroadcast, $channel);
    }

    public function store() {
        $channel = PermissionsHelper::getChannelIfAllowed(request()->input('channel_id'), ['live_broadcast']);
        $broadcast = new Broadcast([
            'channel_id' => $channel->id,
            'user_id' => auth()->user()->id
        ]);
        return $this->fillData($broadcast);
    }

    public function update($id) {
        $broadcast = Broadcast::findOrFail($id);
        PermissionsHelper::check(['live_broadcast'], $broadcast->channel, $broadcast);
        return $this->fillData($broadcast);
    }

    public function destroy($id) {
        $broadcast = Broadcast::notActive()->where(['id' => $id])->firstOrFail();
        PermissionsHelper::check(['live_broadcast'], $broadcast->channel, $broadcast);
        $broadcast->delete();
        return $broadcast;
    }

    private function fillData(Broadcast $broadcast = null, Channel $channel_to_update_metadata = null) {
        $validate_rules = $this->validate_rules;
        if ($broadcast && (!$broadcast->id || ($broadcast->will_start_at))) {
            $validate_rules['will_start_at'] = 'required|date|after_or_equal:now';
            $validate_rules['will_end_at'] = 'required|date|after_or_equal:will_start_at';
        }
        $data = request()->validate($validate_rules);

        // todo: multiple categories, maybe?
        $category_id = $data['category']['id'] ?? null;
        if (!$category_id && !empty($data['category']['name'])) {
            $category = BroadcastCategory::firstOrNew([
                'name' => $data['category']['name']
            ]);
            $category->is_game = !!request()->input('is_game', true);
            $category->save();
            $category_id = $category->id;
        }
        unset($data['category']);
        if ($broadcast) {
            $broadcast->fill($data);
            if (request()->has('tags')) {
                $broadcast->tags = request()->input('tags');
            }
            $broadcast->category_id = $category_id;
            $broadcast->save();
            $active_broadcast = $broadcast->channel->activeBroadcast;
            if ($active_broadcast && $broadcast->id == $active_broadcast->id) {
                $channel_to_update_metadata = $active_broadcast->channel;
            }
        }

        if ($channel_to_update_metadata) {
            $metadata = [
                'title' => $data['title'],
                'description' => $data['description'],
                'category_id' => $category_id,
                'tags' => isset($data['tags']) ? $data['tags'] : []
            ];
            $channel_to_update_metadata->additional_settings = [
                'default_broadcast_metadata' => $metadata
            ];
            $channel_to_update_metadata->save();
            return $metadata;
        }
        unset($broadcast->channel);
        return $broadcast;
    }


}
