<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Helpers\MediaHelper;
use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use App\Models\Playlist;
use App\Models\Media;

use Hidehalo\Nanoid\Client as NanoidClient;

class PlaylistsController extends Controller {

    public function getForChannel($channel_id) {
        $channel = Channel::findOrFail($channel_id);
        $playlists = Playlist::visible()->withCount(['media'])->where(['channel_id' => $channel->id])->orderBy('updated_at', 'desc');
        if (request()->has('search')) {
            $playlists = $playlists->search(request()->input('search'));
        }
        $playlists = $playlists->paginate(request()->input('count', 16));
        return $playlists;
    }

    public function getForManager($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['playlists']);
        $user = auth()->user();

        $playlists = $channel->playlists()->withCount(['likes', 'media'])->where(['channel_id' => $channel->id])->orderBy('updated_at', 'desc');
        if (request()->has('search')) {
            $playlists = $playlists->search(request()->input('search'));
        }

        $has_full_permissions = PermissionsHelper::getStatus(['statistics'], $channel, null, true);
        if (!$has_full_permissions) {
            $playlists = $playlists->where(function($q) use ($user) {
                $q->visible();
                $q->orWhere(['user_id' => $user->id]);
            });
        }
        $playlists = $playlists->paginate(request()->input('count', 16));

        foreach ($playlists as $playlist) {
            $playlist->can_edit = $has_full_permissions || $playlist->user_id === $user->id;
        }
        return $playlists;
    }


    public function getAllByChannel($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['playlists']);
        $user = auth()->user();

        $playlists = $channel->playlists()->orderBy('updated_at', 'desc');
        $has_full_permissions = PermissionsHelper::getStatus(['statistics'], $channel, null, true);
        if (!$has_full_permissions) {
            $playlists = $playlists->where(function($q) use ($user) {
                $q->visible();
                $q->orWhere(['user_id' => $user->id]);
            });
        }
        return $playlists->get();
    }

    public function index() {
        $playlists = Playlist::visible()->orderBy('id', 'desc');
        if (request()->has('search')) {
            $playlists = $playlists->where('name','LIKE',"%".request()->input('search')."%");
        }
        return $playlists->paginate(request()->input('count', 10));
    }

    protected function fillData($playlist) {
        $validation_rules = [
            'name' => 'required|max:255',
            'description' => 'sometimes',
            'pictures_data' => 'sometimes',
            'colors_scheme' => 'sometimes|array',
            'use_custom_design' => 'sometimes|boolean',
            'privacy_status' => 'sometimes|in:0,1,2',
            'links' => 'sometimes|array|nullable',
            'links.*.title'=>'required',
            'links.*.url'=>'required',
        ];
        $data = request()->validate($validation_rules);

        $playlist->fill($data);
        $playlist->save();
        if (request()->has('media_ids')) {
            $media_ids = collect(request()->input('media_ids'));
            $available_ids = Media::where(['channel_id' => $playlist->channel_id])->whereIn('id', $media_ids)->get();

            $playlist->media()->sync([]);
            $sync = [];
            foreach ($media_ids as $index => $media_id) {
                if ($available_ids->contains($media_id)) {
                    $sync[] = [
                        'index' => $index,
                        'media_id' => $media_id
                    ];
                }
            }
            $playlist->media()->sync($sync);
        }
        if (request()->filled('additional_settings')) {
            $playlist->additional_settings = request()->input('additional_settings');
            $playlist->save();
        }
        if (request()->filled('tags')) {
            $playlist->tags = request()->input('tags');
            $playlist->save();
        }
        $playlist->can_edit = true;
        $playlist->load('media');
        return $playlist;
    }

    public function store() {
        $channel = PermissionsHelper::getChannelIfAllowed(request()->input('channel_id'), ['playlists']);
        $user = auth()->user();
        $playlist = new Playlist([
            'uuid' => (new NanoidClient())->generateId(),
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'views' => 0
        ]);
        return $this->fillData($playlist);
    }

    public function show($uuid) {
        $playlist = Playlist::where(['uuid' => $uuid])->firstOrFail();
        $channel = $playlist->channel;
        if (!$channel) {
            return CommonResponses::notFound();
        }
        if ($channel->is_banned) {
            return response()->json(['message' => 'errors.channel_is_banned'], 403);
        }
        if (request()->has('load_additional_settings')) {
            $playlist->append('additional_settings');
        }
      //  StatisticsModule::increment($playlist);
        return $playlist;
    }


    public function update($id) {
        $playlist = Playlist::findOrFail($id);
        PermissionsHelper::check(['playlists'], $playlist->channel, $playlist);
        return $this->fillData($playlist);
    }

    public function destroy($id) {
        $playlist = Playlist::findOrFail($id);
        PermissionsHelper::check(['playlists'], $playlist->channel, $playlist);
        $playlist->delete();
        return [
            'message' => 'dashboard.playlists.messages.playlist_deleted',
        ];
    }

    public function getMedia($uuid) {
        $playlist = Playlist::where(['uuid' => $uuid])->firstOrFail();
        $channel = $playlist->channel;
        if (!$channel) {
            return CommonResponses::notFound();
        }
        if ($channel->is_banned) {
            return response()->json(['message' => 'errors.channel_is_banned'], 403);
        }

        $media = MediaHelper::filterAndSort($playlist->visibleMedia());
        $media->getCollection()->transform(function($media_item) use ($playlist) {
            $media_item->playlist_id = $playlist->uuid;
            $media_item->index_in_playlist = $media_item->pivot->index;
            return $media_item;
        });
        return $media;
    }

}
