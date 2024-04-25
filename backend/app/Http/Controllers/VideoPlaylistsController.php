<?php

namespace App\Http\Controllers;

use App\Models\Announce;
use App\Autopilot\AutopilotFolder;
use App\Autopilot\AutopilotItem;
use App\Autopilot\AutopilotPlaylist;
use App\Autopilot\AutopilotPlaylistData;
use App\Autopilot\AutopilotPlaylistGenerator;
use App\Autopilot\AutopilotTempItem;
use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Helpers\VideoServerAPI;
use App\Jobs\GeneratePlaylistInBackground;


use App\Models\Media;
use Illuminate\Support\Carbon;

class VideoPlaylistsController extends Controller
{

    public function fillData($playlist) {
        $data = new AutopilotPlaylistData();
        $data->fromRequest(request());
        if (!$data->valid) {
            return CommonResponses::validationError($data->errors);
        }
        $playlist->data = json_decode(json_encode($data));
        $playlist->user_id = auth()->user()->id;
        $playlist->save();
        if (request()->has('items')) {
            $index = 0;
            $old_items = AutopilotItem::where(['playlist_id' => $playlist->id])->get();
            $old_folders = AutopilotFolder::where(['playlist_id' => $playlist->id])->get();
            $old_items_data = [];
            $old_folders_data = [];
            $old_ids = [];
            $new_ids = [];
            $old_folder_ids = [];
            $new_folder_ids = [];
            $is_standard = ($data->playlistType === 'default' && $data->playbackType === 'default');

            foreach ($old_items as $item) {
                $old_ids[] = $item->id;
                $old_items_data[$item->id] = $item;
            }
            foreach ($old_folders as $folder) {
                $old_folder_ids[] = $folder->id;
                $old_folders_data[$folder->id] = $folder;
            }
            $items_list = [];
            $items = request()->input('items');
            foreach ($items as $item) {
                if ($item) {
                    $items_list[] = $item;
                }
            }
            foreach ($items_list as $item_data) {
               if (isset($item_data['is_folder']) && (bool)$item_data['is_folder']) {
                    if (isset($item_data['id']) && isset($old_folders_data[$item_data['id']])) {
                        $item =  $old_folders_data[$item_data['id']];
                        $new_folder_ids[] = $item_data['id'];
                        $old_folders_data[$item_data['id']]->fill($item_data);
                        $old_folders_data[$item_data['id']]->index = $index;
                        $old_folders_data[$item_data['id']]->save();
                    } else {
                        $item = new AutopilotFolder($item_data);
                        $item->playlist_id = $playlist->id;
                        $item->index = $index;
                        $item->save();
                    }
                    if (isset($item_data['items']) && is_array($item_data['items']) && count($item_data['items']) > 0) {
                        $folder_index = 0;
                        $folder_items = [];
                        foreach ($item_data['items']  as $folder_item) {
                            if ($folder_item) {
                                $folder_items[] = $folder_item;
                            }
                        }
                        foreach ($folder_items as $folder_item_data) {
                            if (isset($folder_item_data['id']) && isset($old_items_data[$folder_item_data['id']])) {
                                $new_ids[] = $folder_item_data['id'];
                                $old_items_data[$folder_item_data['id']]->fill($folder_item_data);
                                $old_items_data[$folder_item_data['id']]->folder_id = $item->id;
                                $old_items_data[$folder_item_data['id']]->index = $folder_index;
                                $old_items_data[$folder_item_data['id']]->save();
                            } else {
                                $folder_item = new AutopilotItem($folder_item_data);
                                $folder_item->folder_id = $item->id;
                                $folder_item->playlist_id = $playlist->id;
                                $folder_item->index = $folder_index;
                                $folder_item->save();
                            }
                            $folder_index++;
                        }
                    }
                } else {
                    if (isset($item_data['id']) && isset($old_items_data[$item_data['id']])) {
                        $new_ids[] = $item_data['id'];
                        $old_items_data[$item_data['id']]->fill($item_data);
                        $old_items_data[$item_data['id']]->index = $index;
                        $old_items_data[$item_data['id']]->save();
                    } else {
                        $item = new AutopilotItem($item_data);
                        $item->playlist_id = $playlist->id;
                        $item->index = $index;
                        $item->save();
                    }
                }
                $index++;
            }
            $ids_to_delete = array_values(array_diff($old_ids, $new_ids));
            $folder_ids_to_delete = array_values(array_diff($old_folder_ids, $new_folder_ids));
            AutopilotItem::whereIn('id', $ids_to_delete)->delete();
            AutopilotFolder::whereIn('id', $folder_ids_to_delete)->delete();
            $playlist->items = $playlist->items->keys();
            if ($is_standard) {
                $subscriptions = ProgramNotification::where(function($q) use ($new_ids) {
                    $q->where(['is_sent' => false]);
                    $q->where(['program_type' => 'autopilot_item']);
                    $q->whereIn('program_id', $new_ids);
                })->orWhere(function($q) use ($new_folder_ids) {
                    $q->where(['is_sent' => false]);
                    $q->where(['program_type' => 'autopilot_folder']);
                    $q->whereIn('program_id', $new_folder_ids);
                })->get();

                if (count($subscriptions) > 0) {
                    $item_times = [];
                    $time = $data->time_start;
                    foreach ($playlist->items as $item) {
                        $item_times[$item->id] = $time;
                        $time += $item->length;
                    }
                    foreach ($subscriptions as $subscription) {
                        if (isset($item_times[$subscription->program_id])) {
                            $new_time = Carbon::createFromTimestamp($item_times[$subscription->program_id]);
                            $subscription->time = $new_time;
                            $subscription->save();
                        }
                    }
                }
            } else {
                if ($data->playbackType === "default") {
                    $new_time = Carbon::createFromTimestamp($data->time_start);
                    ProgramNotification::where(['program_id' => $playlist->id, 'program_type' => 'autopilot_playlist', 'is_sent' => false])->update([
                        'time' => $new_time
                    ]);
                }
            }
            GeneratePlaylistInBackground::dispatchNow($playlist->channel_id);
        }
        return $playlist;
    }
    public function store() {
        PermissionsHelper::check( ['autopilot']);
        $playlist = new AutopilotPlaylist([
            'channel_id' => request()->input('channel_id')
        ]);
        return $this->fillData($playlist);
    }

    public function update($id) {
        $playlist = AutopilotPlaylist::findOrFail($id);
        PermissionsHelper::check( ['autopilot'], $playlist->channel, $playlist);
        return $this->fillData($playlist);
    }

    public function destroy($id) {
        $playlist = AutopilotPlaylist::findOrFail($id);
        PermissionsHelper::check( ['autopilot'], $playlist->channel, $playlist);
        $playlist->delete();
        GeneratePlaylistInBackground::dispatchNow($playlist->channel_id);
        return $playlist;
    }

    public function generate($id) {
        $time_start = request()->input('time', time());
        $time_end = $time_start + 86400;
        $settings = [
            'remove_old' => true
        ];
        AutopilotTempItem::where(['channel_id' => $id])->delete();
        $playlist = AutopilotPlaylistGenerator::generateForChannel($id, $time_start, $time_end, $settings);
        if ($special_server = VideoServerAPI::getSpecialServer($id)) {
            $response = VideoServerAPI::request('update_playlists', [
                [
                    'data' => json_encode($playlist),
                    'channel_id' => $id
                ]
            ], $special_server);
        } else {
            $response = VideoServerAPI::requestAll('update_playlists', [
                [
                    'data' => json_encode($playlist),
                    'channel_id' => $id
                ]
            ], VideoServerAPI::getSpecialServersList());
        }
        return $playlist;
    }

    public function getNextItems($id) {
        $count = request()->input('count', 10);
        $items = AutopilotTempItem::where(['channel_id' => $id])->where('time_start', '>=', time())->orderBy('time_start', 'asc')->limit($count)->get();
        foreach ($items as $item) {
            $autopilot_item = AutopilotItem::find($item->item_id);
            if ($autopilot_item) {
                $item->video = Media::find($autopilot_item->video_id);
            }
        }
        return $items;
    }

}
