<?php
namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;

use App\Models\Audio;
use App\Models\Channel;
use App\Models\Folder;

use App\Helpers\RadioAPI;

use App\Helpers\LocalizationHelper;

class FoldersController extends Controller
{
    public $validation_rules = [
        'channel_id' => 'required',
        'folder_title' => 'required|max:255',
        'folder_description' => 'sometimes|max:1000',
    ];


    public function store(LocalizationHelper $languages) {
        PermissionsHelper::check(['live_broadcast']);
        $data = request()->validate($this->validation_rules, LocalizationHelper::translate('dashboard.tracks.add_folder._error_data'));
        $channel = Channel::findOrFail(request()->input('channel_id'));
        $user = auth()->user();
        if (request()->filled('parent_id')) {
            $parent_folder = Folder::findOrFail($data['parent_id']);
            if ($parent_folder->channel_id !== $channel->id) {
                return CommonResponses::unauthorized();
            }
        }
        $folder = new Folder($data);
        $folder->user_id = $user->id;
        $folder->channel_id = $channel->id;

        $radio_api = new RadioAPI();
        $current_server = $radio_api->getServerByChannelId($channel->id);
        $folder->radio_server = $current_server->id;
        $folder->save();

        $channel_folder_path =  $current_server->storage_path."/$channel->id/";
        $folder_path = $channel_folder_path.$folder->id;
        $folder->radio_server_path = $folder_path;
        $folder->save();

            //$ftp = Storage::disk('ftp_'.$current_server);
            //$ftp->makeDirectory($folder_path);

        return $folder;
    }


    public function destroy($id)
    {
        $folder = Folder::findOrFail($id);
        PermissionsHelper::check(['media'], $folder->channel, $folder);

        $move_tracks = false;
        $move_tracks_to = request()->input('move_tracks_to');
        $delete_tracks = !!request()->input('delete_tracks', false);

        $folder_to_move = Folder::find($move_tracks_to);
        if ($move_tracks_to !== -1 && (!$folder_to_move || ($folder_to_move->channel_id !== $folder->channel_id))) {
            return CommonResponses::unauthorized();
        }

        $tracks = Audio::where(['channel_id' => $folder->channel_id])->get();
        foreach ($tracks as $track) {
            $folders = $track->folders;
            if (in_array($id, $folders)) {
                if (count($folders) === 1) {
                    if ($delete_tracks) {
                        $track->delete();
                    } else {
                        if ($move_tracks) {
                            $folders = array_diff($folders, [$id]);
                            if (!in_array($move_tracks_to, $folders)) {
                                $folders[] = $move_tracks_to;
                            }
                            $track->folders = $folders;
                            $track->save();
                        }
                    }
                } else {
                    $track->folders = array_diff($folders, [$id]);
                    $track->save();
                }
            }
        }
        $folder->delete();
        $total_size = Audio::where(['channel_id' => $folder->channel_id])->sum('file_size');
        return [
            'message' => 'global.deleted',
            'space_occupied' => $total_size
        ];
    }


}
