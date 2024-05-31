<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Helpers\MediaHelper;
use App\Models\Folder;
use App\Models\Media;
use App\Models\MediaFolder;

class MediaFoldersController extends Controller {

    private function fillData($folder) {
        $data = request()->validate([
            'title' => 'required|string',
            'is_private' => 'sometimes|boolean'
        ]);
        if (isset($data['is_private'])) {
            $data['is_public'] = !$data['is_private'];
            unset($data['is_private']);
        }
        $folder->fill($data);

        if (request()->has('parent_id')) {
            if (request()->input('parent_id') !== null) {
                $parent = MediaFolder::findOrFail(request()->input('parent_id'));
                if ($parent->channel_id != $folder->channel_id) {
                    return CommonResponses::unauthorized();
                }
                if ($folder->id) {
                    $subfolder_ids = MediaHelper::getSubFolders($folder->id);
                    if (in_array($parent->id, $subfolder_ids)) {
                        return CommonResponses::validationError(['title' => ['dashboard.videos.folders.errors.wrong_structure']]);
                    }
                }
                $folder->parent_id = $parent->id;
            } else {
                $folder->parent_id = null;
            }

        }
        $folder->save();
        return $folder;
    }



    public function store($channel_id){
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['media']);
        $folder = new MediaFolder([
            'user_id' => auth()->user()->id,
            'channel_id' => $channel->id,
        ]);
        return $this->fillData($folder);
    }

    public function update($channel_id, $id) {
        $folder = MediaFolder::findOrFail($id);
        PermissionsHelper::check(['media'], $folder->channel, $folder);
        return $this->fillData($folder);
    }

    public function index($channel_id) {
        PermissionsHelper::check(['media'], $channel_id);
        $folders = MediaFolder::where(['channel_id' => $channel_id])->orderBy('updated_at', 'desc')->get();
        return $folders;
    }

    public function getBreadcrumbs($channel_id, $id) {
        $folder = MediaFolder::findOrFail($id);
        PermissionsHelper::check(['media'], $folder->channel, $folder);
        $breadcrumbs = [[
            'id' => $folder->id,
            'title' => $folder->title
        ]];
        while ($folder->parent_id) {
            $folder = Folder::find($folder->parent_id);
            $breadcrumbs[] = [
                'id' => $folder->id,
                'title' => $folder->title
            ];
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        return $breadcrumbs;
    }


    public function destroy($channel_id, $id) {
        $folder = MediaFolder::findOrFail($id);
        PermissionsHelper::check(['playlists', 'records'], $folder->channel, $folder);

        if (request()->input('action') == 'move_up') {
            MediaFolder::where(['parent_id' => $folder->id])->update(['parent_id' => $folder->parent_id]);
            Media::where(['folder_id' => $folder->id])->update([
                'folder_id' => $folder->parent_id
            ]);
            $folder->delete();
        } else {
            $subfolder_ids = MediaHelper::getSubFolders($id);
            MediaFolder::whereIn('id', $subfolder_ids)->delete();
            $media = Media::whereIn('folder_id', $subfolder_ids)->get();
            foreach ($media as $media_item) {
                MediaHelper::deleteMedia($media_item);
            }
        }
        return $folder;
    }
}
