<?php

namespace App\Http\Controllers;


use App\Helpers\CommonResponses;
use App\Models\StreamKey;

class RadioStreamController extends Controller
{
    public function auth($channel_id) {
        file_put_contents(public_path("logs/radio_auth.txt"),json_encode(request()->all()));
        $key = StreamKey::where(['stream_key' => request()->input('pass'), 'channel_id' => $channel_id])->first();
        if ($key) {
            $channel = Channel::find($channel_id);
            $channel_online = Broadcast::where(['channel_id' => $id])->first();
            if ($channel_online) {
                $channel_online->user_id = $key->user_id;
                $channel_online->is_live = true;
                $channel_online->save();
            } else {
                $server = (new RadioAPI())->getServerByChannelId($channel->id);
                $channel_online = new Broadcast();
                $channel_online->channel_id = $channel->id;
                $channel_online->server_id = $server->id;
                $channel_online->user_id = $key->user_id;
                $channel_online->is_live = true;
                $channel_online->save();
            }
            if (time() - $channel->wasonline >= 10 * 60) {
                Notification::where(['entity_id' => $channel->id, 'entity_type' => 'channels'])->delete();
                (new ChannelGotOnline($channel, $channel_online))->send($channel->id, 30);
            }
            //event(new ChannelIsOnlineEvent($channel, $channel_online));
            //$channel->wasonline = time();
            //$channel->save();

            return "1";
        } else {
            return "0";
        }
    }

    public function saveRecord($id) {
        file_put_contents(public_path("logs/radio_records.txt"),json_encode(request()->all()));

        if (request()->filled('path')) {
            $path = request()->input('path');

            $channel = Channel::find($id);

            $channel_online = Broadcast::where(['channel_id' => $id])->first();
            if ($channel_online) {
                $channel_online->is_live = false;
                $channel_online->save();
            }

            event(new ChannelLiveStreamEndedEvent($channel)); // todo: change
            $record_all = $channel->additional_settings['records']['record_all'];
            $is_visible = $channel->additional_settings['records']['records_visible'];

            if ($record_all) {
                $path_data = explode("/", $path);
                $radio_api = new RadioAPI();
                $current_server = $radio_api->getServerByChannelId($channel->id);
                $ftp = Storage::disk('ftp_' . $current_server);

                $track = new Audio();
                $track->channel_id = $id;
                $track->uploaded_by = -1;
                $track->folder_id = -1;
                $track->file_size = $ftp->size($path);
                if ($track->file_size < 1024) {
                    return "0";
                    return [
                        'status' => 0,
                        'text' => 'TOO_SMALL_SIZE'
                    ];
                }
                $track->upload_status = 1;
                $track->filename = $path_data[count($path_data) - 1];
                $track->title = str_replace("_", "-", explode(".", $track->filename)[0]);
                $track->length = 0;

                $track->is_public = $is_visible;
                $track->radio_server = $current_server;
                $track->radio_server_path = $path;
                $track->save();


                return "1";
            }
            return "0";
        } else {
            return "0";
        }
    }

    public function getState($id) {
        $is_online = Broadcast::where(['channel_id'=>$id])->count() > 0;
        return [
            'status'=>1,
            'data' => [
                'is_online'=>$is_online
            ]
        ];
    }

    public function setState($id) {
        if (PermissionsHelper::check(['live_broadcast'], $id) || !Channel::find($id)) {
            $state = (bool)(request()->filled('state') ? request()->input('state') == "true" : false);
            if(Audio::where(['channel_id'=>$id])->count() == 0) {
                return [
                    'status'=>0,'is_online'=>!$state,'text'=>'radio_stream.errors.no_tracks'
                ];
            }

            $radio_api = new RadioAPI();
            $action = $state ? "start_station" : "stop_station_by_id";
            $server = $radio_api->getServerByChannelId($id);
            //$create_script_request = (new RadioAPI())->action($server,"create_script",$id);
            $api_request = $radio_api->action($server, $action, $id);
            if ($state) {
                $channel_online_entity = new Broadcast([
                    'is_live' => false,
                    'channel_id'=>$id,
                    'server_id'=>$server->id,
                    'online_data'=>''
                ]);
                $channel_online_entity->save();
            } else {
                Broadcast::where(['channel_id'=>$id])->delete();
            }
            if ($api_request && $api_request->status == 1) {
                return ['is_online'=>$state, 'server_response' => $api_request];
            } else {
                $res = ['status'=>0,'is_online'=>$state,'text'=>'radio_stream.errors.server_error'];
                if ($api_request && isset($api_request->text)) {
                    $res['error_code'] = $api_request->text;
                }
                return $res;
            }
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function getNextTrack($id) {
        if (request()->filled('_reset')) {
            RadioPlaylist::where(['channel_id'=>$id])->update([
                'last_play_index' => null,
                'last_play_time' => null,
                'last_play_track_index' => null
            ]);
            RadioPlaybackHistoryItem::where(['channel_id'=>$id])->delete();
        }

        $queue_track = RadioQueueItem::where(['channel_id' => $id, 'was_played' => false])->orderBy('index', 'ASC')->first();

        if ($queue_track) {
            $track = $queue_track->track;
            $last_play_index = RadioPlaylist::where(['channel_id'=>$id])->max('last_play_index');
            $radio_playback_history_item = new RadioPlaybackHistoryItem();
            $radio_playback_history_item->local_index = $last_play_index + 1;
            $radio_playback_history_item->channel_id = $id;
            $radio_playback_history_item->radio_file_id = $track->id;
            $radio_playback_history_item->save();
            $queue_track->was_played = true;
            $queue_track->play_time = time();
            $queue_track->save();
            event(new ChannelNewRadioTrack($id, $track, [
                'is_queue' => true,
                'queue_track_id' => $queue_track->id,
            ]));
            return response('annotate:title="'.$track->title.'",artist="'.$track->author.'":'.$track->radio_server_path, 200)->header('Content-Type', 'text/plain; charset=utf-8');
        }
        $playlists = RadioPlaylist::where(['channel_id'=>$id])->get();
        $current_playlists = [];
        $last_play_index = RadioPlaylist::where(['channel_id'=>$id])->max('last_play_index');
        $last_playlist = RadioPlaylist::where(['last_play_index'=>$last_play_index])->first();
        $last_play_item =  RadioPlaybackHistoryItem::where(['local_index'=>$last_play_index])->first();
        $next_specified_playlist_times = [];

        foreach ($playlists as $playlist) {
            $playback_data = $playlist->playback_data;
            if ($playlist->playback_order == 2 && $playlist->last_play_track_index > -1 && $playlist->last_play_track_index < $playback_data->tracks_count - 1) {
                $current_playlists[$playlist->id] = ['type'=>$playlist->playback_type,'data'=>$playlist,'weight'=>$playlist->playback_weight,'inner_weight'=>5];
            } else {
                if ($playlist->playback_type == 1) {
                    $current_playlists[$playlist->id] = ['type' => 1, 'data' => $playlist, 'weight' => $playlist->playback_weight, 'inner_weight' => 1];
                } elseif ($playlist->playback_type == 2) {
                    $day_of_week = date('w', time()) + 1;
                    if (isset($playback_data->play_times->{$day_of_week})) {
                        $current_day_playback_data = $playback_data->play_times->{$day_of_week};
                        $hours_start = explode(":", $current_day_playback_data->play_from)[0];
                        $hours_end = explode(":", $current_day_playback_data->play_till)[0];
                        $minutes_start = explode(":", $current_day_playback_data->play_from)[1];
                        $minutes_end = explode(":", $current_day_playback_data->play_till)[1];
                        $day_start = strtotime("midnight", time());
                        $in_interval = ((($day_start + $hours_start * 60 + $minutes_start) < time()) && (time() < ($day_start + $hours_end * 60 + $minutes_end)));
                        if ($in_interval) {
                            $current_playlists[$playlist->id] = ['type' => 2, 'data' => $playlist, 'weight' => $playlist->playback_weight, 'inner_weight' => 1];
                        }
                    }
                } elseif ($playlist->playback_type == 3) {
                    if (time() - $playlist->last_play_time >= ($playback_data->minutes_between * 60)) {
                        $current_playlists[$playlist->id] = ['type' => 3, 'data' => $playlist, 'weight' => $playlist->playback_weight, 'inner_weight' => 2];
                    }
                } elseif ($playlist->playback_type == 4) {
                    $real_tracks_between = $playback_data->tracks_between;
                    if ($last_play_index - $playlist->last_play_index >= $real_tracks_between) {
                        $current_playlists[$playlist->id] = ['type' => 4, 'data' => $playlist, 'weight' => $playlist->playback_weight, 'inner_weight' => 2];
                    }
                } elseif ($playlist->playback_type == 5) {
                    $day_of_week = (int)date('w', time());
                    if (isset($playback_data->play_times->{$day_of_week})) {
                        $need_start = false;
                        $next_start_time = strtotime('midnight', time());
                        $play_in = $playback_data->play_times->{$day_of_week}->play_in;
                        $play_in = explode(":", $play_in);
                        $next_start_time += $play_in[0] * 3600 + $play_in[1] * 60;
                        if (isset($playback_data->timezone_offset)) {
                            $next_start_time += $playback_data->timezone_offset * 60;
                        }
                        if ($next_start_time <= time()) {
                            if (($playlist->last_play_track_index == null) || ($playlist->last_play_track_index == -1)) {
                                $need_start = true;
                            }
                            if ($playlist->last_play_date === null || $playlist->last_play_date != date('d.m.Y', time())) {
                                $need_start = true;
                                $playlist->last_play_track_index = null;

                            }
                        }
                        if ($need_start) {
                            $current_playlists[$playlist->id] = ['type' => 5, 'data' => $playlist, 'weight' => $playlist->playback_weight, 'inner_weight' => 3];
                        } else {
                            $next_specified_playlist_times[] = $next_start_time;
                        }
                    }
                } elseif ($playlist->playback_type == 6) {
                    $offset = isset($playback_data->timezone_offset) ? $playback_data->timezone_offset : 0;
                    $time_start = $playback_data->custom_time_start + $offset;
                    $time_end = $playback_data->custom_time_end + $offset;
                    if ($time_start <= time() && time() <= $time_end) {
                        $current_playlists[$playlist->id] = ['type' => 6, 'data' => $playlist, 'weight' => $playlist->playback_weight, 'inner_weight' => 3];
                    } else {
                        if ($time_start > time()) {
                            $next_specified_playlist_times[] = $time_start;
                        }
                    }
                }
                if (isset($playback_data->tracks_count) && $playlist->last_play_track_index >= $playback_data->tracks_count) {
                    $playlist->last_play_track_index = -1;
                    $playlist->save();
                }
            }
        }

        $current_playlists = collect($current_playlists);
        $current_playlists = $current_playlists->filter(function ($playlist, $key) {
            $data = $playlist['data'];
            if (count($data->tracks) === 0 && count($data->folders) === 0) {
                return false;
            }
            return true;
        });

        $special_playlists = $current_playlists->sortByDesc('data.playback_type')->filter(function($playlist, $key) {
            $data = $playlist['data'];
            return $data->playback_type >= 5 || $data->playback_order == 2;
        });
        $current_playlist = null;
        if (count($special_playlists) > 0) {
            $current_playlist = $special_playlists->values()[0]['data'];
        } else {


            //$max_weight = $current_playlists->max('inner_weight');
            //$current_playlists = $current_playlists->filter(function ($playlist, $key) use ($max_weight) {
            //    return $playlist['inner_weight'] == $max_weight;
            //});

            $probabilities = $current_playlists->pluck('weight');
            $all_probabilities = $current_playlists->sum('weight');
            if ($all_probabilities === 0) {
                //
            } else {
                $rand = mt_rand(0, $all_probabilities - 1);
                $i = 0;

                foreach ($probabilities as $probability) {
                    if ($i <= $rand && $rand < ($i + $probability)) {
                        $current_playlist = $current_playlists->values()->get($i)['data'];
                    }
                    $i += $probability;
                }
            }
        }
        if ($current_playlist) {
            $request_tracks = RadioRequest::where(['playlist_id' => $current_playlist->id, 'status' => -1])->get();
            if (count($request_tracks) > 0) {
                $tracks = [];
                foreach ($request_tracks as $request_track) {
                    $track = Audio::find($request_track->track_id);
                    if ($track) {
                        $track->request_id = $request_track->id;
                        $tracks[] = $track;
                    }
                }
                $tracks = collect($tracks);
            } else {
                $init_tracks = $current_playlist->tracks;
                $folders = $current_playlist->folders;
                $tracks = $init_tracks;
                $tracks = $tracks->filter(function ($track) {
                    return $track->upload_status == "STATUS_READY";
                });
                foreach ($folders as $folder) {
                    $folder_tracks = $this->getFolderTracks($folder);
                    $tracks = $tracks->merge($folder_tracks);
                }
            }
            $track = null;
            $last_play_track_index = null;
            if ($current_playlist->playback_order == 0) {
                if (count($tracks) > 1 && $last_play_item) {
                    $tracks = $tracks->filter(function ($track) use ($last_play_item) {
                        return $track->id !== $last_play_item->radio_file_id;
                    });
                }
                $random_index = mt_rand(0, count($tracks) - 1);
                $last_play_track_index = $random_index;
                $track = $tracks->values()[$random_index];
            } elseif ($current_playlist->playback_order == 1) {
                $last_play_track_index =  $current_playlist->last_play_track_index === null ? 0 : $current_playlist->last_play_track_index + 1;
                $values = $tracks->values();
                if (isset($values[$last_play_track_index])) {
                    $track = $values[$last_play_track_index];
                } else {
                    $last_play_track_index = 0;
                    $track = $values[0];
                }
            } elseif ($current_playlist->playback_order == 2) {
                if (count($tracks) > 1 && $last_play_item) {
                    $tracks = $tracks->filter(function ($track) use ($last_play_item) {
                        return $track->id !== $last_play_item->radio_file_id;
                    });
                }
                $last_play_track_index = $current_playlist->last_play_track_index + 1;
                $random_index = mt_rand(0, count($tracks) - 1);
                $track = $tracks->values()[$random_index];
            }
            if ($current_playlist->playback_type == 5) {
                $current_playlist->last_play_track_index = $current_playlist->last_play_track_index !== null ?  $current_playlist->last_play_track_index + 1 : 0;
            }
            $length_limit = null;

            foreach ($next_specified_playlist_times as $time) {
                $next = $time - time();
                if ($next < $track->length) {
                    $length_limit = $next;
                }
            }
           if (isset($track->request_id)) {
                RadioRequest::where(['id' => $track->request_id])->update(['status' => 1]);
            }
            if ($length_limit) {
                $track->length = $length_limit;
            }
            $items_in_history = 10;

            RadioPlaybackHistoryItem::where(['channel_id' => $id])->where('local_index', '<=', $last_play_index - $items_in_history)->delete();
            $radio_playback_history_item = new RadioPlaybackHistoryItem();
            $radio_playback_history_item->local_index = $last_play_index + 1;
            $radio_playback_history_item->channel_id = $track->channel_id;
            $radio_playback_history_item->radio_file_id = $track->id;
            $radio_playback_history_item->playlist_id = $current_playlist->id;
            $radio_playback_history_item->save();
            $current_playlist->last_play_date = date('d.m.Y', time());
            $current_playlist->last_play_index = $last_play_index + 1;
            $current_playlist->last_play_time = time();
            $current_playlist->last_play_track_index = $last_play_track_index;
            $current_playlist->save();//liq_cue_in=0,liq_cue_out=30,
            return response('annotate:'.($length_limit ? "liq_cue_in=0,liq_cue_out=$length_limit," : '').'title="'.$track->title.'",artist="'.$track->author.'":'.$track->radio_server_path, 200)->header('Content-Type', 'text/plain; charset=utf-8');
        } else {
            return 'annotate:title="Station offline",artist="Catcast.tv":/var/radio/storage1/blank.mp3';
        }
    }

    public function getFolderTracks($folder) {
        $tracks = Audio::where(['folder_id' => $folder->id, 'channel_id' => $folder->channel_id, 'upload_status' => 1])->get();
        $folders = Folder::where(['parent_id' => $folder->id])->get();
        foreach ($folders as $child_folder) {
            $child_tracks = $this->getFolderTracks($child_folder);
            $tracks = $tracks->merge($child_tracks);
        }
        return $tracks;
    }

    public function getCurrentTrack($id) {
        $channel_online_entity = Broadcast::where(['channel_id'=>$id])->first();
        if ($channel_online_entity) {
            $last_track = RadioPlaybackHistoryItem::where(['channel_id'=>$id])->orderBy('created_at','desc')->first();
            $server = $channel_online_entity->server;
            $server_ip = $server->ip_address;
            $playback_url = "https://$server_ip/radio/$id.mp3";
            $source = RadioAPI::getSource($server,$id);
            $listeners = 0;
            if ($source) {
                $listeners = $source->listeners;
                if (isset($source->title) && $source->title != "" && $source->title != " - ") {
                    if ($channel_online_entity->is_live) {
                        $last_track = null;
                    }
                }
            }
           // $track = RadioAPI::getCurrentTrack($server,$id);
            //dd($track);
            $channel = Channel::find($id);
            if ($channel->is_temp_stopped) {
                (new RadioAPI())->action($server, "start_station", $channel->id);
                $channel->is_temp_stopped = false;
            }
            $channel->last_listen_time = time();
            $channel->save();
             if ($last_track) {
                $data = [
                    'status' => 1,
                    'data' => [
                        'is_online' => true,
                        'playback_url' => $playback_url,
                        'listeners_count' => $listeners,
                        'was_temp_stopped' => $channel->is_temp_stopped,

                        'playlist' => $last_track->playlist
                    ]
                ];
                if ($last_track->track) {
                    $data['data']['track'] = [
                        'id' => $last_track->track->id,
                        'title' => $last_track->track->title,
                        'artist' => $last_track->track->author
                    ];
                } else {
                    $data['data']['track'] = [
                        'id' => $last_track->radio_file_id,
                        'title' => "Unknown title",
                        'artist' => "Unknown author"
                    ];
                }
            } else {
                $data =  [
                    'status' => 1,
                    'data' => [
                        'is_online' => true,
                        'playback_url' => $playback_url,
                        'listeners_count' => $listeners,
                        'track' => [
                            'title' => 'Unknown track',
                            'artist' => 'Unknown user_id'
                        ]
                    ]
                ];
            }
            if (isset($source->title)) {
                $source->title = html_entity_decode($source->title);
                $title = explode("-", $source->title);
                if (count($title) === 2) {
                    $data['data']['track']['title'] = trim($title[0]);
                    $data['data']['track']['artist'] = trim($title[1]);
                } else {
                    $data['data']['track']['title'] = trim($title[0]);
                    $data['data']['track']['artist'] = "";
                }
            }
            return $data;
        } else {
            return [
                'status' => 1,
                'data' => [
                    'is_online' => false
                ]
            ];
        }
    }

    public function redirect($id) {
        $action = "start_station";
        $server = (new RadioAPI())->getServerByChannelId($id);
        $channel_online_entity = Broadcast::where(['channel_id'=>$id])->first();
        if (!$channel_online_entity) {
            $api_request = (new RadioAPI())->action($server, $action, $id);
            sleep(0.5);
        }
        $url = "http://v1.catcast.tv:10045/36644";
        return redirect($url);
    }

    public function request($id) {
        $channel = Channel::findOrFail($id);
        if ($channel->is_banned) {
            return [
                'status' => 0,
                'text'=>'errors.channel_is_banned'
            ];
        }
        if (!$channel->is_radio) {
            return [
                'status'=>0,
                'text'=>'errors.wrong_channel_type'
            ];
        }
        $last_track = RadioPlaybackHistoryItem::where(['channel_id'=>$id])->orderBy('created_at','desc')->first();
        if (!$last_track) {
            return CommonResponses::unauthorized();
        }
        $playlist = $last_track->playlist;
        if (!$playlist) {
            return CommonResponses::unauthorized();
        }
        if (!$playlist->can_accept_requests) {
            return CommonResponses::unauthorized();
        }
        $track_id = request()->input('track_id');
        $has_track = false;
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

        foreach ($tracks as $track) {
            if ($track->id == $track_id) {
                $has_track = true;
            }
        }
        if (!$has_track) {
            return CommonResponses::unauthorized();
        }
        if (!$user = auth()->user())  {
            return CommonResponses::unauthorized();
        }
        $requests = RadioRequest::where(['user_id' => $user->id, 'channel_id' => $channel->id, 'status' => -1])->get();
        if (count($requests) >= 3) {
            return [
                'status' => 0,
                'text' => 'radio_player.request_panel.errors.too_many_requests'
            ];
        }
        $request = new RadioRequest();
        $request->user_id = $user->id;
        $request->playlist_id = $playlist->id;
        $request->track_id = $track_id;
        $request->channel_id = $channel->id;
        $request->status = -1;
        $request->save();
        return [
            'status' => 1,
            'text' => 'radio_player.request_panel.messages.success_added'
        ];
    }
}
