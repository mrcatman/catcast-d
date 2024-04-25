<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Models\Audio;
use App\Models\Channel;

use App\Models\Folder;
use App\Models\RadioPlaylist;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use App\Helpers\Strings;
use App\Helpers\LocalizationHelper;

class RadioPlaylistsController extends Controller
{
    public $validation_rules = [
        'title' => 'required|max:255',
        'description' => 'sometimes|max:1000',
        'channel_id'=>'required',
        'playback_weight'=>'min:0|max:10',
        'playback_type'=>'min:0|max:6',
        'playback_order'=>'min:0|max:1',
        'cover'=>'sometimes',
    ];

    public function getValidationRules($playback_type) {
        $validation_rules = $this->validation_rules;
        if ($playback_type === 3) {
            $validation_rules['playback_data.minutes_between'] = "numeric|min:10|max:3600";
        }
        if ($playback_type === 4) {
            $validation_rules['playback_data.tracks_between'] = "numeric|min:1|max:1000";
        }
        if ($playback_type === 2) {
            $validation_rules['playback_data.play_times.*.play_from'] = "required|date_format:H:i";
            $validation_rules['playback_data.play_times.*.play_till'] = "required|date_format:H:i";
        }
        if ($playback_type === 5) {
            $validation_rules['playback_data.play_times.*.play_in'] = "required|date_format:H:i";
        }
        if ($playback_type === 6) {
            $validation_rules['playback_data.custom_time_start'] = 'required|numeric';
            $validation_rules['playback_data.custom_time_end'] = 'required|numeric';
        }
        return $validation_rules;
    }

    public function getForChannel($channel_id) {
        PermissionsHelper::check(['live_broadcast'], $channel_id);
        $playlists = RadioPlaylist::where(['channel_id' => $channel_id])->orderBy('created_at','desc')->get();
        return $playlists;
    }

    public function store() {
        PermissionsHelper::check(['live_broadcast']);
        $channel = Channel::findOrFail(request()->input('channel_id'));
        if (!$channel->is_radio) {
            return CommonResponses::unauthorized();
        }
        $validation_rules = $this->getValidationRules(request()->input('playback_type'));
        $data = request()->validate($validation_rules, LocalizationHelper::getFormErrors('dashboard.tracks.playlists._error_data'));
        $playlist = new RadioPlaylist($data);
        $offset = (int)request()->header('X-Timezone-Offset', 0);
        $data = json_decode($playlist->playback_data);
        if (!$data) {
            $data = (object)[];
        }
        $data->timezone_offset = $offset;
        $playlist->is_visible = (bool)request()->input('is_visible', false);
        $playlist->playback_data = $data;
        $playlist->created_by = auth()->user()->id;
        $playlist->is_special = false;
        $playlist->save();
        return $playlist;
    }

    public function update($id) {
        $playlist = RadioPlaylist::findOrFail($id);
        PermissionsHelper::check(['live_broadcast'], $playlist->channel, $playlist);
        $validation_rules = $this->getValidationRules(request()->input('playback_type'));
        $data = request()->validate($validation_rules, LocalizationHelper::getFormErrors('dashboard.tracks.playlists._error_data'));
        $playlist = RadioPlaylist::find($id);
        if ($playlist->channel_id !== request()->input('channel_id')) {
            return CommonResponses::unauthorized();
        }
        $playlist->fill($data);
        $offset = (int)request()->header('X-Timezone-Offset');
        $data = (object)$playlist->playback_data;
        $data->timezone_offset = $offset;
        $playlist->playback_data = $data;
        $playlist->save();
        return $playlist;
    }


    public function destroy($id) {
        $playlist = RadioPlaylist::findOrFail($id);
        PermissionsHelper::check(['live_broadcast'], $playlist->channel, $playlist);
        $playlist->delete();
        return $playlist;
    }

    public function batchSave($id) {
        $playlist = RadioPlaylist::findOrFail($id);
        PermissionsHelper::check(['live_broadcast'], $playlist->channel, $playlist);

        if (request()->has('data')) {
            $data = request()->input('data');
            DB::transaction(function () use ($playlist, $data) {
                $ids = [];
                foreach ($data as $index => $track_data) {
                    if ($index == 1) {
                        DB::table('radio_files_playlists')->where(['playlist_id' => $playlist->id])->delete();
                        DB::table('radio_folders_playlists')->where(['playlist_id' => $playlist->id])->delete();
                    }
                    $ids[] = $index;
                };
                DB::table('radio_files_playlists')->where(['playlist_id' => $playlist->id])->whereIn('index', $ids)->delete();
                DB::table('radio_folders_playlists')->where(['playlist_id' => $playlist->id])->whereIn('index', $ids)->delete();

                $items = [];
                $folders = [];

                foreach ($data as $index => $track_data) {
                    if ((!isset($track_data['is_folder']) || !$track_data['is_folder']) && isset($track_data['id'])) {
                        $items[] = [
                            'radio_file_id' => $track_data['id'],
                            'playlist_id' => $playlist->id,
                            'index' => $index
                        ];
                    } else {
                        $folders[] = [
                            'radio_folder_id' => $track_data['id'],
                            'playlist_id' => $playlist->id,
                            'index' => $index
                        ];
                    }
                }
                DB::table('radio_files_playlists')->insert($items);
                DB::table('radio_folders_playlists')->insert($folders);
            });
            return ['message' => 'global.saved'];
        } else {
            return response()->json(['message' => 'errors.fields_empty'], 422);
        }
    }

    public function autocomplete($id) {
        $autocomplete = request()->input('search');
        if (!$autocomplete || mb_strlen($autocomplete, 'UTF-8') < 3) {
            return [];
        }
        $playlist = RadioPlaylist::findOrFail($id);
        if (!$playlist->can_accept_requests) {
            return CommonResponses::unauthorized();
        }
        $autocomplete = mb_strtolower($autocomplete, "UTF-8");
        $init_tracks = $playlist->tracks;
        $folders = $playlist->folders;
        $tracks = $init_tracks;
        $tracks = $tracks->filter(function($track) {
            return $track->upload_status == "STATUS_READY";
        });
        foreach ($folders as $folder) {
            $folder_tracks = $this->getFolderTracks($folder);
            $tracks = $tracks->merge($folder_tracks);
        }

        $tracks = $tracks->map(function($track) {
            $track->full_title = $track->author." - ".$track->title;
            return $track;
        });
        $tracks = $tracks->filter(function($track) use ($autocomplete) {
           return mb_strpos(mb_strtolower($track->full_title, "UTF-8"), $autocomplete, 0, "UTF-8") !== false;
        });

        return $tracks;
    }

    protected function getFolderTracks($folder) {
        $tracks = Audio::where(['folder_id' => $folder->id, 'channel_id' => $folder->channel_id, 'upload_status' => 1])->get();
        $folders = Folder::where(['parent_id' => $folder->id])->get();
        foreach ($folders as $child_folder) {
            $child_tracks = $this->getFolderTracks($child_folder);
            $tracks = $tracks->merge($child_tracks);
        }
        return $tracks;
    }


}
