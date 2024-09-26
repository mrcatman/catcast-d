<?php

namespace App\Http\Controllers;

use App\Helpers\CategoriesHelper;
use App\Helpers\FiltersHelper;
use App\Helpers\PermissionsHelper;
use App\Models\Broadcast;
use App\Models\Category;
use App\Models\Channel;

class BroadcastsController extends Controller{

    private $validate_rules = [
        'title' => 'required',
        'description' => 'sometimes',
        'tags' => 'sometimes|array'
    ];

    public function show($id)
    {
        return Broadcast::findOrFail($id);
    }

    public function getByChannel($channel_id){
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['live_broadcast']);

        $broadcasts = FiltersHelper::applyFromRequest($channel->broadcasts(), Broadcast::class);

        $full_permissions = PermissionsHelper::getStatus(['live_broadcast'], $channel);
        $has_statistics_permissions = PermissionsHelper::getStatus(['statistics'],$channel);

        $user = auth()->user();
        if ($user) {
            $broadcasts->getCollection()->transform(function ($broadcast) use ($user, $full_permissions, $has_statistics_permissions) {
                $broadcast->can_delete = $full_permissions || $broadcast->user_id == $user->id;
                $broadcast->can_edit = $full_permissions || $broadcast->user_id == $user->id;
                $broadcast->can_view_statistics = $has_statistics_permissions && $broadcast->can_edit;
                return $broadcast;
            });
        }
        return $broadcasts;
    }

    public function getActive($channel_id) {
        $channel = Channel::findOrFail($channel_id);
        $active_broadcast = $channel->activeBroadcast;
        $data = $active_broadcast ?: $channel->additional_settings['default_broadcast_metadata'];
        $data['is_online'] = !!$active_broadcast;

        if (!$active_broadcast && $data['category_id']) {
            $data['category'] = Category::find($data['category_id']);
        }
        $data['can_edit'] = PermissionsHelper::getStatus(['live_broadcast'], $channel, $active_broadcast);
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
            $validate_rules['will_end_at'] = 'sometimes|date|after_or_equal:will_start_at';
        }
        $data = request()->validate($validate_rules);

        if ($broadcast) {
            $broadcast->fill($data);
            if (request()->filled('tags')) {
                $broadcast->tags = request()->input('tags');
            }
            $broadcast->category_id = CategoriesHelper::getIdFromRequest();
            $active_broadcast = $broadcast->channel->activeBroadcast;
            if ($active_broadcast && $broadcast->id == $active_broadcast->id) {
                $channel_to_update_metadata = $active_broadcast->channel;
            }
        }

        if ($channel_to_update_metadata) {
            $metadata = [
                'title' => $data['title'],
                'description' => $data['description'],
                'category_id' => CategoriesHelper::getIdFromRequest(),
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
