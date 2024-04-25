<?php

namespace App\Http\Controllers;

use App\Enums\PrivacyStatuses;
use App\Helpers\CommonResponses;
use App\Helpers\RelationsHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\MediaHelper;
use App\Jobs\DownloadExternalVideo;
use App\Models\Channel;
use App\Models\Folder;
use App\Models\Picture;
use App\Models\Playlist;
use App\Models\Tag;
use App\Models\MediaUploadKey;

use App\Models\Media;
use App\Models\MediaFolder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Hidehalo\Nanoid\Client as NanoidClient;

class MediaController extends Controller {

    private function checkAccess($media) {
        if (!in_array($media->privacy_status, [PrivacyStatuses::PUBLIC, PrivacyStatuses::UNLISTED]) && !PermissionsHelper::getStatus(['records'], $media->channel)) {
            return false;
        }
        return true;
    }

    public function show($uuid) {
        $media = Media::where(['uuid' => $uuid])->firstOrFail();
        if (!$this->checkAccess($media)) {
            return CommonResponses::unauthorized();
        }
        $channel = $media->channel;
        if (!$channel) {
            return CommonResponses::notFound();
        }
        if ($channel->is_blocked) {
            return CommonResponses::unauthorized();
        }
        $media->append('playlist_ids');
        unset($media->channel);
        unset($media->playlists); // todo
        if (request()->has('load_additional_settings')) {
            $media->append('additional_settings');
        }
        if (request()->has('load_permissions')) {
            $media->permissions = MediaHelper::getPermissions($media);
        }
        return $media;
    }


    public function getRelated($uuid) {
        $media = Media::where(['uuid' => $uuid])->firstOrFail();
        if (!$this->checkAccess($media)) {
            return CommonResponses::unauthorized();
        }
        $channel = $media->channel;
        if (!$channel) {
            return CommonResponses::notFound();
        }
        if ($channel->is_blocked) {
            return response()->json(['message' => 'errors.channel_blocked'], 403);
        }

        $media = Media::visible()->where(['channel_id' => $channel->id])->where('id', '!=', $media->id)->inRandomOrder();
        $media = $media->paginate(request()->input('count', 10));
        $media->getCollection()->transform(function($media) use ($channel) {
            $media->channel = $channel;
            return $media;
        }); // todo: recommendations
        return $media;
    }

    public function fileManager($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['media']);
        $order = request()->input('order', 'date_desc');
        $order_field = 'id';
        $order_dir = 'desc';
        switch ($order) {
            case 'date_desc':
                $order_field = 'id';
                break;
            case 'date_asc':
                $order_dir = 'asc';
                break;
            case 'views_desc':
                $order_field = 'views';
                break;
            case 'views_asc':
                $order_field = 'views';
                $order_dir = 'asc';
                break;
            default:
                break;
        }

        $media = DB::table(with(new Media)->getTable())->where([
            'channel_id' => $channel->id
        ])->orderBy($order_field, $order_dir);
        $folders = DB::table(with(new MediaFolder)->getTable())->where([
            'channel_id' => $channel->id
        ])->orderBy($order_field, $order_dir);

        if (!request()->has('search')) {
            if (request()->has('folder_id')) {
                $media = $media->where(['folder_id' => request()->input('folder_id')]);
                $folders = $folders->where(['parent_id' => request()->input('folder_id')]);
            }  else {
                $media = $media->whereNull('folder_id');
                $folders = $folders->whereNull('parent_id');
            }
        } else {
            $search = request()->input('search', '');
            if ($search != '') {
                $media = $media->where(function($query) use ($search) {
                    $query->where('title', 'LIKE', '%'.$search.'%');
                    $query->orWhere('description', 'LIKE', '%'.$search.'%');
                });
                $folders = $folders->where('title', 'LIKE', '%'.$search.'%');
            }
            if (request()->has('folder_id')) {
                $folder_ids = MediaHelper::getSubFolders(request()->input('folder_id'));
                $folders = $folders->whereIn('id', $folder_ids);
                $media = $media->whereIn('folder_id', $folder_ids);
            }
        }

        $result = $media->select(['id'])->selectRaw('false as is_folder')->selectRaw('views as views')
            ->union($folders->select(['id'])->selectRaw('true as is_folder')->selectRaw('0 as views'))->orderBy('is_folder', 'desc')->orderBy($order_field, $order_dir)->paginate(16);

        $result->getCollection()->transform(function($item) {

            if ($item->is_folder) {
                $object = Folder::find($item->id);
                $permissions = [
                    'can_edit' => true, // todo: change
                    'can_view_statistics' => false
                ];
            } else {
                $object = Media::find($item->id);
                $object->append('total_files_size');
                $permissions = MediaHelper::getPermissions($object);
            }
            return [
                'is_folder' => (bool)$item->is_folder,
                'permissions' => $permissions,
                'object' => $object,
            ];
        });

        return $result;
    }

    public function getDiskSpace($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['media']);
        $space_occupied = $channel->getOccupiedDiskSpace();
        $space_total = $channel->getTotalDiskSpace();
        return [
            'space_occupied' => $space_occupied,
            'space_total' => $space_total,
        ];
    }


    public function index() {
        $media = Media::visible();
        switch (request()->input('show')) {
            case 'feed':
               $media = $media->fromLikedChannelsAndPlaylists();
               break;
            case 'recommended':
                $media = $media->recommended();
                break;
            case 'now_watching':
                $media = $media->nowWatching();
                break;
            case 'most_liked':
                $media = $media->mostLiked();
                break;
            case 'most_watched':
                $media = $media->mostWatched();
                break;
            default:
                break;
        }
        return MediaHelper::filterAndSort($media);
    }


    public function getForChannel($id) {
        $channel = Channel::findOrFail($id);
        $media = Media::where(['channel_id' => $channel->id])->visible();
        return MediaHelper::filterAndSort($media);
    }


    public function getInfoByURL() {
        if (auth()->user()) {
            try {
                return MediaHelper::getExternalInfo(request()->input('url'));
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        } else {
            return CommonResponses::unauthorized();
        }
    }


    public function store() {
        $channel = PermissionsHelper::getChannelIfAllowed(request()->input('channel_id'), ['media']);
        if (request()->filled('file_size')) {
            $file_size = (int)request()->input('file_size');
            if ($channel->getOccupiedDiskSpace() + $file_size > $channel->getTotalDiskSpace()) {
                return response()->json([
                    'message' => 'dashboard.media.errors.not_enough_space',
                    'not_enough_space' => true
                ], 422);
            }
        }
        $user = auth()->user();

        $type = Media::getPreferredType($channel);

        $media = new Media([
            'uuid' => (new NanoidClient())->generateId(),
            'media_type' => $type,
            'user_id' => $user->id,
            'channel_id' => $channel->id,
            'privacy_status' => PrivacyStatuses::PRIVATE
        ]);
        return $this->fillData($media);
    }

    public function update($id) {
        $media = Media::findOrFail($id);
        PermissionsHelper::check(['media'], $media->channel, $media);
        return $this->fillData($media);
    }

    protected function fillData($media) {
        $data = request()->validate([
            'title' => 'sometimes|max:256',
            'description' => 'sometimes|max:1024',
            'privacy_status' => 'sometimes|in:0,1,2'
        ]);

        if (empty($data['title'])) {
            $data['title'] = 'Media '.date('d.m.Y H:i:s', time());
        }
        $media->fill($data);
        $media->save();

        RelationsHelper::fillIfExists($media, 'folder_id', MediaFolder::class, true);
        RelationsHelper::fillIfExists($media, 'thumbnail_id', Picture::class);
        if (request()->has('playlist_ids')) {
            $playlist_ids = Playlist::where(['channel_id' => $media->channel_id])->whereIn('id', request()->input('playlist_ids'))->pluck('id');
            $media->playlistsOfHomeChannel()->sync($playlist_ids);
        }
        if (request()->filled('additional_settings')) {
            $media->additional_settings = request()->input('additional_settings');
        }
        if (request()->filled('tags')) {
            $media->tags = request()->input('tags');
        }
        $media->save();

        return $media;
    }

    public function upload($id) {
        $media = Media::findOrFail($id);
        PermissionsHelper::check(['media'], $media->channel, $media);
        $key = new MediaUploadKey([
            'media_id' => $media->id,
            'key' => Str::random(18)
        ]);
        $key->save();
        return $key;
    }

    public function externalUpload($id) {
        $media = Media::findOrFail($id);
        PermissionsHelper::check(['media'], $media->channel, $media);
        $data = request()->validate([
            'url' => 'required|url',
        ]);
        $class = $media->channel->is_radio ? DownloadExternalVideo::class : DownloadExternalVideo::class; // todo: audio
        $class::dispatch($media, $data['url']);
        return $media;
    }


    public function destroy($id) {
        $media = Media::findOrFail($id);
        MediaHelper::deleteMedia($media);
        return $media;
    }

    public function bulkMove($channel_id) {
        $channel =  PermissionsHelper::getChannelIfAllowed($channel_id, ['media']);
        $move_to = request()->input('move_to');
        if ($move_to === -1) {
            $move_to = null;
        } else {
            $folder = MediaFolder::findOrFail($move_to);
            if ($folder->channel_id != $channel->id) {
                return CommonResponses::unauthorized();
            }
        }

        $media_ids = request()->input('media_ids', []);
        $media = Media::where(['channel_id' => $channel->id])->whereIn('id', $media_ids)->get();
        $folder_ids = request()->input('folder_ids', []);
        $folders = MediaFolder::where(['channel_id' => $channel->id])->whereIn('id', $folder_ids)->get();

        $media->each(function($media) use ($move_to) {
            $media->update(['folder_id' => $move_to]);
        });
        $folders->each(function($folder) use ($move_to) {
            $subfolder_ids = MediaHelper::getSubFolders($folder->id);

            if (!in_array($move_to, $subfolder_ids)) {
                $folder->update(['parent_id' => $move_to]);
            }
        });
        return [
            'message' => count($folders) > 0 ? 'dashboard.media.messages.media_mass_moved_folders' : 'dashboard.media.messages.media_mass_moved',
        ];
    }

    public function bulkDelete($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['media']);
        $media_ids = request()->input('media_ids', []);
        $media = Media::where(['channel_id' => $channel->id])->whereIn('id', $media_ids)->get();
        $folder_ids = request()->input('folder_ids', []);
        $folders = MediaFolder::where(['channel_id' => $channel->id])->whereIn('id', $folder_ids)->get();

        foreach ($media as $media_item) {
            MediaHelper::deleteMedia($media_item);
        }
        foreach ($folders as $folder) {
            if (request()->input('action') == 'move_up') {
                MediaFolder::where(['parent_id' => $folder->id])->update(['parent_id' => $folder->parent_id]);
                Media::where(['folder_id' => $folder->id])->update([
                    'folder_id' => $folder->parent_id
                ]);
            } else {
                $subfolder_ids = MediaHelper::getSubFolders($folder->id);
                MediaFolder::whereIn('id', $subfolder_ids)->delete();
                $media = Media::whereIn('folder_id', $subfolder_ids)->get();
                foreach ($media as $media_item) {
                    MediaHelper::deleteMedia($media_item);
                }
            }
            $folder->delete();
        }

        return [
            'message' => count($folders) > 0 ? 'dashboard.media.messages.media_mass_deleted_folders' : 'dashboard.media.messages.media_mass_deleted',
        ];
    }

    public function getTags() {
        $limit = request()->input('limit', 24);

        $tags = Tag::groupBy('tag')
            ->select(['tags.*', DB::raw('COUNT(*) as count')])
            ->orderByRaw('COUNT(*) DESC')
            ->where(['entity_type' => 'media']);


        $tags = $tags->limit($limit)->get();
        $data = [];
        foreach ($tags as $tag) {
            $data[] = [
                'tag' => $tag->tag,
                'count' => $tag->count
            ];
        }
        return $data;
    }
}
