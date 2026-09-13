<?php

namespace App\Autopilot;

use App\Models\Channel;
use App\Helpers\VideoServerAPI;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class AutopilotPlaylistGenerator {

    public static function getChannelIdsToUpdate($time_start, $time_end) {
        ini_set('memory_limit', '5000M');
        ini_set('max_execution_time', '2000');
        AutopilotItem::where('length','<=', '0')->delete();
        $all_playlists = AutopilotPlaylist::orderBy('id','desc')->get();
        $all_playlists = $all_playlists->filter(function ($playlist) use ($time_start, $time_end) {
            echo 'Playlist id: '.$playlist->id.' Channel id: '.$playlist->channel_id.PHP_EOL;
            if ($playlist->extended_data->time_start <= $time_end) {
                $type = $playlist->extended_data->playlistType;
                $playback_type = $playlist->extended_data->playbackType;
                if ($type === "default" && $playback_type === "default") {
                    if ($playlist->extended_data->cycled) {
                        $till = $playlist->extended_data->cycledTill;
                        if ($till > $time_start) {
                            return true;
                        }
                    } else {
                        $playlist_time_start = $playlist->extended_data->time_start;
                        $playlist_time_end = $playlist_time_start;
                        $playlist_items = $playlist->extended_data->getItems();
                        foreach ($playlist_items as $item) {
                            $playlist_time_end+=$item->length;
                        }
                        if ($playlist_time_end > $time_start) {
                            return true;
                        }
                    }
                } else {
                    return true;
                }
            }
            return false;
        });
        $channel_ids = $all_playlists->pluck('channel_id')->unique();
        $channel_ids = $channel_ids->toArray();
       // $channels_filtered = Channel::where('last_watched_at', '>=', time() - 60 * 60 * 24 * 10)->whereIn('id', $channel_ids)->pluck('id');
        return $channel_ids;
    }

    public static function generateAll($settings) {
        $time_start = time() - 1;
        $time_end = time() + 86400;
        \App\Autopilot\AutopilotTempItem::where('time_end', '<', time() - 60 * 60 * 8)->delete();
        $channel_ids = self::getChannelIdsToUpdate($time_start, $time_end);

        echo implode(",", $channel_ids).PHP_EOL;
      //  file_put_contents(public_path('logs/scheduler_'.time().'.txt'),json_encode($channel_ids));
        $filename  = 'logs/scheduler_'.time().'.txt';
        $last_generate_time = time();
        foreach ($channel_ids as $channel_id) {
            try {
                file_put_contents(public_path($filename), "[".date('H:i:s')."] Generating playlist for: ".$channel_id.PHP_EOL, FILE_APPEND | LOCK_EX);
                if ($channel_id == 270) {
                    $time_end = time() + 7 * 86400;
                }
                $generated_playlist = AutopilotPlaylistGenerator::generateForChannel($channel_id, $time_start, $time_end, $settings);
                file_put_contents(public_path($filename), "[".date('H:i:s')."] Items count: ".count($generated_playlist)." Seconds: ".(time() - $last_generate_time).PHP_EOL, FILE_APPEND | LOCK_EX);
                $last_generate_time = time();
                if ($generated_playlist) {
                    if ($special_server = VideoServerAPI::getSpecialServer($channel_id)) {
                        echo "Server: " . $special_server . " Channel id: " . $channel_id . PHP_EOL;
                        $response = VideoServerAPI::request('update_playlists', [
                            [
                                'data' => json_encode($generated_playlist),
                                'channel_id' => $channel_id
                            ]
                        ], $special_server);
                        echo "Response: " . json_encode($response) . PHP_EOL;
                    } else {
                        try {
                            $response = VideoServerAPI::requestAll('update_playlists', [
                                [
                                    'data' => json_encode($generated_playlist),
                                    'channel_id' => $channel_id
                                ]
                            ], VideoServerAPI::getSpecialServersList());
                            echo "Response: " . json_encode($response) . PHP_EOL;
                        } catch (\Exception $e) {
                            echo "Error";
                        }
                    }
                } else {
                    echo "Not generated playlist";
                }
            } catch (\Exception $e) {
                echo "Error when generating playlist for ".$channel_id;
            }
        }
    }

    public static function generateForChannel($id, $time_start, $time_end, $settings = []){
        if (request()->has('test')) {
            //dd(AutopilotTempItem::where(['channel_id' => $id])->get());
        }
        if (request()->has('remove_all')) {
            AutopilotTempItem::where(['channel_id' => $id])->delete();
        }
        if (isset($settings['verbose']) && $settings['verbose']) {
            echo "updating playlists for ".$id.PHP_EOL;
        }
        $playlists = AutopilotPlaylist::where(['channel_id' => $id])->get();
        $real_start_times = [];
        $playlists = $playlists->filter(function ($playlist) use ($time_end) {
            return $playlist->extended_data->time_start <= $time_end;
        });
        $playlists_data = [];
        foreach ($playlists as $playlist) {
            $playlists_data[$playlist->id] = $playlist;
        }
        $real_first_item_time = null;
        $first_item_index = 0;

        $old_playlists = AutopilotPlaylist::where(['channel_id' => $id])->pluck('id');
        $old_items = AutopilotItem::whereIn('playlist_id', $old_playlists)->pluck('id');
        AutopilotTempItem::where(['channel_id' => $id])->whereNotIn('item_id', $old_items)->delete();
        //AutopilotTempItem::where(['channel_id' => $id, 'is_atc' => true])->delete();
        $current_item = AutopilotTempItem::where(['channel_id' => $id])->where('time_start', '>=', $time_start)->orderBy('time_start', 'asc')->first();

        if (isset($settings['remove_old']) && $settings['remove_old']) {
            //$current_item = AutopilotTempItem::where(['channel_id' => $id])->where('time_start', '<=', time())->where('time_end', '>=', time())->first();
	        $now_item = AutopilotTempItem::where(['channel_id' => $id])->where('time_start', '<=', time())->where('time_end','>=',time())->orderBy('time_start', 'asc')->first();

            if ($current_item && $now_item) {
                $first_item_index = $current_item->clip_index ? $current_item->clip_index : 0;

                $first_item_index++;
                if (!request()->has('test')) {
                    AutopilotTempItem::where(['channel_id' => $id])->where('clip_index', '>=', $first_item_index)->delete();
                    AutopilotTempItem::where(['channel_id' => $id])->where('clip_index', '=', $first_item_index - 1)->where('id', '!=', $current_item->id)->delete();
                }
                if($current_item->repeat_count > 0) {
                    $first_item_index+=$current_item->repeat_count;
                } else {
                    $real_first_item_time = $time_start;
                }
                $real_first_item_time = $current_item->time_end;
            } else {
                AutopilotTempItem::where(['channel_id' => $id])->delete();
            }
        } else {
            $last_item = AutopilotTempItem::where(['channel_id' => $id])->orderBy('time_end', 'DESC')->first();
            if ($last_item) {
                $first_item_index = $last_item->clip_index ? $last_item->clip_index : 0;
                $real_first_item_time = $last_item->time_end;
            }
            //if ($current_item) {
            //    $current_playlist = $playlists_data[$current_item->playlist_id];
            //}
        }

        AutopilotTempItem::where(['channel_id' => $id])->where('time_start', '<', $time_start - 86400)->delete();

        foreach ($playlists as $playlist) {
            $type = $playlist->extended_data->playbackType;
            if ($type === "default") {
                $real_start_times[$playlist->extended_data->time_start] = $playlist->id;
            } elseif ($type === "repeating") {
                $repeating_day_start = strtotime(date('d.m.Y', $time_start) . ' 00:00');
                $playback_time_start = $playlist->extended_data->playbackTimeStart;
                $seconds_from_day_start = (int)$playback_time_start * 60;
                while ($repeating_day_start < $time_end) {
                    $week_day = date('N', $repeating_day_start);
                    if ($playlist->extended_data->playbackDays->{$week_day}) {
                        $playback_start = (int)($repeating_day_start + $seconds_from_day_start);
                        $real_start_times[$playback_start] = $playlist->id;
                    }
                    $repeating_day_start += 86400;
                }
            } else {

            }
        }
        ksort($real_start_times);

        // foreach ($real_start_times as $start_time => $playlist_id) {
           // var_dump($playlist_id,$start_time - $last_item->time_end);
        // }

        $items = [];
        $playlist_index = 0;
        foreach ($real_start_times as $start_time => $playlist_id) {
            $next_playlist_start = $playlist_index + 1 < count($real_start_times) ? array_keys($real_start_times)[$playlist_index + 1] : 0;
            $playlist = $playlists_data[$playlist_id];
            $type = $playlist->extended_data->playlistType;
            $current_time_marker = $start_time;
            if ($playlist->extended_data->playbackType !== "around_the_clock") {

                if ($type === "mixer") {
                    $playback_limit_type = $playlist->extended_data->playbackLimitType;
                    $playback_limit_value = $playlist->extended_data->playbackLimitValue;
                    $need_to_add = true;
                    $playlist_items = $playlist->extended_data->getItems();

                    if (count($playlist_items) === 0) {
                        $need_to_add = false;
                    }
                    if ($playback_limit_type === "by_number") {
                        for ($i = 1; $i <= $playback_limit_value; $i++) {
                            if ($need_to_add) {
                                $next_playlist_in = $next_playlist_start - $current_time_marker;
                                $playlist_item = $playlist->extended_data->getRandomItem();
                                $item = [
                                    'playlist_id' => $playlist->id,
                                    'data' => $playlist_item,
                                    'length' => $playlist_item->length,
                                    'time_start' => $current_time_marker,
                                    'time_end' => $current_time_marker + $playlist_item->length
                                ];
                                if ($next_playlist_in - $playlist_item->length <= 0 && $next_playlist_start > 0) {
                                    $need_to_add = false;
                                    $playback_trim_type = $playlist->extended_data->playbackTrimType;
                                    if ($playback_trim_type === "default") {
                                        $item['trim_length'] = $item['length'] - $next_playlist_in;
                                        $item['length'] = $next_playlist_in;
                                        $item['time_end'] = $current_time_marker + $next_playlist_in;
                                        if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                            $items[] = $item;
                                        }
                                        $current_time_marker += $next_playlist_in;
                                    } elseif ($playback_trim_type === "limit_prev") {

                                    } elseif ($playback_trim_type === "move_next") {
                                        $old_time_before_move = array_keys($real_start_times)[$playlist_index + 1];
                                        $new_time_after_move = $old_time_before_move + ($current_time_marker + $next_playlist_in);
                                        $id_to_move = $real_start_times[$old_time_before_move];
                                        unset($real_start_times[$old_time_before_move]);
                                        $real_start_times[$new_time_after_move] = $id_to_move;
                                        if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                            $items[] = $item;
                                        }
                                    }
                                } else {
                                    if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                        $items[] = $item;
                                    }
                                    $current_time_marker += $playlist_item->length;
                                }
                            }
                        }
                    } else {
                        $playlist_time_end = $current_time_marker;
                        if ($playback_limit_type === "by_minutes") {
                            $playlist_time_end = $current_time_marker + $playback_limit_value * 60;
                        } elseif ($playback_limit_type === "by_date") {
                            $playlist_time_end = $playback_limit_value;
                        } elseif ($playback_limit_type === "by_time") {
                            $minutes = $playlist->extended_data->playbackLimitValueMinutes - $playlist->extended_data->playbackTimeStart;
                            if ($minutes < 0) {
                                $minutes = 3600 + $minutes;
                            }
                            $playlist_time_end = $current_time_marker + $minutes * 60;
                        }
                        if (strlen($playlist_time_end) === 13) {
                            //$playlist_time_end = ceil($playlist_time_end / 1000);
                        }

                        if ($playlist_time_end > $time_end) {
                            //$playlist_time_end = $time_end;
                        }
                        if ($playback_limit_type === "by_time") {
                           // var_dump(date('d.m.y H:i', $start_time), date('d.m.y H:i', $current_time_marker), date('d.m.y H:i', $playlist_time_end));
                        }
                        if (!$next_playlist_start) {
                            $next_playlist_start = $time_end + 1;
                        }
                       while ($current_time_marker < $playlist_time_end && $need_to_add) {
                           if ($need_to_add) {
                                $next_playlist_in = $next_playlist_start - $current_time_marker;
                                $playlist_item = $playlist->extended_data->getRandomItem();
                                $item = [
                                    'playlist_id' => $playlist->id,
                                    'data' => $playlist_item,
                                    'length' => $playlist_item->length,
                                    'time_start' => $current_time_marker,
                                    'time_end' => $current_time_marker + $playlist_item->length
                                ];
                                //  echo (($next_playlist_in - $playlist_item->length <= 0 && $next_playlist_start > 0) ? '1' : '0');
                                if ($next_playlist_in - $playlist_item->length <= 0 && $next_playlist_start > 0) {
                                    $need_to_add = false;
                                    $playback_trim_type = $playlist->extended_data->playbackTrimType;
                                    if ($playback_trim_type === "default") {
                                        $item['trim_length'] = $item['length'] - $next_playlist_in;
                                        $item['length'] = $next_playlist_in;
                                        $item['time_end'] = $current_time_marker + $next_playlist_in;
                                        if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                            $items[] = $item;
                                        }
                                        $current_time_marker += $next_playlist_in;
                                    } elseif ($playback_trim_type === "limit_prev") {

                                    } elseif ($playback_trim_type === "move_next") {
                                        $old_time_before_move = array_keys($real_start_times)[$playlist_index + 1];
                                        $new_time_after_move = $old_time_before_move + ($current_time_marker + $next_playlist_in);
                                        $id_to_move = $real_start_times[$old_time_before_move];
                                        unset($real_start_times[$old_time_before_move]);
                                        $real_start_times[$new_time_after_move] = $id_to_move;
                                        if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                            $items[] = $item;
                                        }
                                    }
                                } else {
                                    if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {

                                       $items[] = $item;
                                    }
                                    $current_time_marker += $playlist_item->length;
                                }
                            }
                        }
                    }
                } elseif ($type === "default") {
                    if ($playlist->extended_data->playbackType !== "around_the_clock") {
                        if ($playlist->extended_data->playbackType === "default" && $playlist->extended_data->cycled) {
                            $playlist_time_end = $playlist->extended_data->cycledTill;

                            if ($playlist_time_end > $time_end) {
                                $playlist_time_end = $time_end;
                            }
                            $i = 0;
                            $playlist_items = $playlist->extended_data->getItems();

                            $need_to_add = true;
                            if (count($playlist_items) === 0) {
                                $need_to_add = false;
                            }


                            while ($current_time_marker < $playlist_time_end && $need_to_add) {
                                $next_playlist_in = $next_playlist_start - $current_time_marker;
                                if ($i >= count($playlist_items)) {
                                    $i = 0;
                                }
                                if (isset($playlist_items[$i])) {
                                    $playlist_item = $playlist_items[$i];
                                    $item = [
                                        'playlist_id' => $playlist->id,
                                        'data' => $playlist_item,
                                        'length' => $playlist_item->length,
                                        'time_start' => $current_time_marker,
                                        'time_end' => $current_time_marker + $playlist_item->length
                                    ];

                                    if ($next_playlist_in - $playlist_item->length <= 0 && $next_playlist_start > 0) {
                                        $need_to_add = false;
                                        $playback_trim_type = $playlist->extended_data->playbackTrimType;
                                        if ($playback_trim_type === "default") {
                                            $item['trim_length'] = $item['length'] - $next_playlist_in;
                                            $item['length'] = $next_playlist_in;
                                            $item['time_end'] = $current_time_marker + $next_playlist_in;
                                            if ($item['time_start'] >= $real_first_item_time) {
                                                $items[] = $item;
                                            }
                                            $current_time_marker += $next_playlist_in;
                                        } elseif ($playback_trim_type === "limit_prev") {

                                        } elseif ($playback_trim_type === "move_next") {
                                            $old_time_before_move = array_keys($real_start_times)[$playlist_index + 1];
                                            $new_time_after_move = $old_time_before_move + ($current_time_marker + $next_playlist_in);
                                            $id_to_move = $real_start_times[$old_time_before_move];
                                            unset($real_start_times[$old_time_before_move]);
                                            $real_start_times[$new_time_after_move] = $id_to_move;
                                            if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                                $items[] = $item;
                                            }

                                        }
                                    } else {

                                        if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                            $items[] = $item;
                                        }
                                        $current_time_marker += $playlist_item->length;
                                    }
                                }
                                $i++;
                            }


                        } else {
                            $playlist_items = $playlist->extended_data->getItems();
                            $need_to_add = true;
                            if (count($playlist_items) === 0) {
                                $need_to_add = false;
                            }
                            foreach ($playlist_items as $playlist_item) {
                                if ($need_to_add) {
                                    $next_playlist_in = $next_playlist_start - $current_time_marker;
                                    $item = [
                                        'playlist_id' => $playlist->id,
                                        'data' => $playlist_item,
                                        'length' => $playlist_item->length,
                                        'time_start' => $current_time_marker,
                                        'time_end' => $current_time_marker + $playlist_item->length
                                    ];
                                    if ($next_playlist_in - $playlist_item->length <= 0 && $next_playlist_start > 0) {
                                        $need_to_add = false;
                                        $playback_trim_type = $playlist->extended_data->playbackTrimType;
                                        if ($playback_trim_type === "default") {
                                            $item['trim_length'] = $item['length'] - $next_playlist_in;
                                            $item['length'] = $next_playlist_in;
                                            $item['time_end'] = $current_time_marker + $next_playlist_in;
                                            if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                                $items[] = $item;
                                            }
                                            $current_time_marker += $next_playlist_in;
                                        } elseif ($playback_trim_type === "limit_prev") {

                                        } elseif ($playback_trim_type === "move_next") {
                                            $old_time_before_move = array_keys($real_start_times)[$playlist_index + 1];
                                            $new_time_after_move = $old_time_before_move + ($current_time_marker + $next_playlist_in);
                                            $id_to_move = $real_start_times[$old_time_before_move];
                                            unset($real_start_times[$old_time_before_move]);
                                            $real_start_times[$new_time_after_move] = $id_to_move;
                                            if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                                $items[] = $item;
                                            }
                                        }
                                    } else {
                                        if (!$real_first_item_time || $item['time_start'] >= $real_first_item_time) {
                                            $items[] = $item;
                                        }
                                        $current_time_marker += $playlist_item->length;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $playlist_index++;
        }


        //$lengths_by_playlist = [];
        //foreach ($items as $item) {
        //    if (!isset($lengths_by_playlist[$item['playlist_id']])) {
        //        $lengths_by_playlist[$item['playlist_id']] = $item['length'];
        //    } else {
        //        $lengths_by_playlist[$item['playlist_id']] += $item['length'];
        //    }
        //}

        $items = array_values(collect($items)->filter(function($item) use ($time_start) {
           return $item['time_start'] > $time_start - 2 * 3600;
        })->toArray());

        $around_the_clock = $playlists->firstWhere('extended_data.playbackType', 'around_the_clock');
//        $around_the_clock_playlists = $playlists->filter(function($playlist) {
//            return $playlist->extended_data->playbackType == 'around_the_clock';
//        });

        if ($around_the_clock) {
            $items_with_new = [];
            if (count($items) === 0) {
                if (!$real_first_item_time) {
                    $real_first_item_time = time();
                }

                $break_length = $time_end - $real_first_item_time;

                if ($break_length > 0) {
                    $added_items_length = 0;
                    $index = 0;
                    $playlist_items = $around_the_clock->extended_data->getItems();
                    $type = $around_the_clock->extended_data->playlistType;


                    if ($current_item) {
                        $current_playlist_is_atc = $around_the_clock->id == $current_item->playlist_id;
                        if ($current_playlist_is_atc) {
                            $item_index = $playlist_items->search(function ($playlist_item) use($current_item) {
                                return $playlist_item->id == $current_item->item_id;
                            });
                            if ($item_index) {
                                $index = $item_index + 1;
                            }
                        }
                    }

                    if (count($playlist_items) > 0) {
                        $last_random_item_ids = [];
                        $do_not_repeat_count = (int)floor(count($playlist_items) / 2);
                         while ($added_items_length < $break_length) {
                            if ($type === "default") {
                                if ($index >= count($playlist_items)) {
                                    $index = 0;
                                }
                                $current_item_data = $playlist_items[$index];
                                $index++;
                            } else {
                                foreach ($playlist_items as &$item) {
                                    $item->inner_id = $item->inner_id ? $item->inner_id : $item->id;
                                }
                                $filtered_items = $playlist_items->filter(function($item) use ($last_random_item_ids) {
                                    return !in_array($item->inner_id, $last_random_item_ids);
                                });

                                $current_item_data = $filtered_items->random();
                                if (count($last_random_item_ids) >= $do_not_repeat_count) {
                                    array_pop($last_random_item_ids);
                                }
                                $last_random_item_ids[] = $current_item_data->inner_id;

                            }

                            $current_item = [
                                'playlist_id' => $around_the_clock->id,
                                'data' => $current_item_data,
                                'length' => $current_item_data->length,
                                'time_start' => $real_first_item_time + $added_items_length,
                                'time_end' => $real_first_item_time + $added_items_length + $current_item_data->length
                            ];
                            if ($added_items_length + $current_item_data->length > $break_length) {
                               // $current_item['trim_length'] = $current_item['length'] - ($break_length - $added_items_length);
                               // $current_item['length'] = $break_length - $added_items_length;
                               // $current_item['time_end'] = $real_first_item_time + $break_length + $current_item['length'];
                            }
                            if (!$real_first_item_time || $current_item['time_start'] >= $real_first_item_time) {
                                $items_with_new[] = $current_item;
                            }
                            $added_items_length += $current_item_data->length;
                        }


                    }
                }

            } else {
                if ($real_first_item_time && $real_first_item_time != $items[0]['time_start']) {
                    $break_length = $items[0]['time_start'] - $real_first_item_time;
                    if ($break_length && $break_length > 0) {
                        $added_items_length = 0;
                        $index = 0;
                        $playlist_items = $around_the_clock->extended_data->getItems();
                        $type = $around_the_clock->extended_data->playlistType;
                        if (count($playlist_items) > 0) {
                            while ($added_items_length < $break_length) {
                                if ($type === "default") {
                                    if ($index >= count($playlist_items)) {
                                        $index = 0;
                                    }
                                    $current_item_data = $playlist_items[$index];
                                    $index++;
                                } else {
                                    $current_item_data = $playlist_items->random();
                                }

                                $current_item = [
                                    'playlist_id' => $around_the_clock->id,
                                    'data' => $current_item_data,
                                    'length' => $current_item_data->length,
                                    'time_start' => $real_first_item_time + $added_items_length,
                                    'time_end' => $real_first_item_time + $added_items_length + $current_item_data->length
                                ];
                                if ($added_items_length + $current_item_data->length > $break_length) {
                                    $current_item['trim_length'] = $current_item['length'] - ($break_length - $added_items_length);
                                    $current_item['length'] = $break_length - $added_items_length;
                                    $current_item['time_end'] = $real_first_item_time + $break_length + $current_item['length'];
                                }

                                $items_with_new[] = $current_item;
                                $added_items_length += $current_item_data->length;
                            }
                            $real_first_item_time += $break_length;
                        }
                    }
                }

                $first_break_length =  $items[0]['time_start'] - $time_start;
                if ($first_break_length > 0 && false) {
                    $first_items = [];
                    $added_items_length = 0;
                    $index = 0;
                    $playlist_items = $around_the_clock->extended_data->getItems();
                    $type = $around_the_clock->extended_data->playlistType;
                    while ($added_items_length < $first_break_length) {
                        if ($type === "default") {
                            if ($index >= count($playlist_items)) {
                                $index = 0;
                            }
                            $current_item_data = $playlist_items[$index];
                            $index++;
                        } else {
                            $current_item_data = $playlist_items->random();
                        }

                        $current_item = [
                            'playlist_id' => $around_the_clock->id,
                            'data' => $current_item_data,
                            'length' => $current_item_data->length,
                            'time_start' => $time_start + $added_items_length,
                            'time_end' => $time_start + $added_items_length + $current_item_data->length
                        ];
                        if ($added_items_length + $current_item_data->length > $first_break_length) {
                            $current_item['trim_length'] = $current_item['length'] - ($first_break_length - $added_items_length);
                            $current_item['length'] = $first_break_length - $added_items_length;
                            $current_item['time_end'] = $time_start + $first_break_length + $current_item['length'];
                        }
                        $current_item['is_atc'] = true;
                        $first_items[] = $current_item;
                        $added_items_length += $current_item_data->length;
                    }
                    if($first_items[count($first_items) - 1]['time_end'] > $items[0]['time_start']) {
                        $first_items[count($first_items) - 1]['trim_length'] = $first_items[count($first_items) - 1]['time_end'] - $items[0]['time_start'];
                        $first_items[count($first_items) - 1]['length'] = $first_items[count($first_items) - 1]['time_end'] - $first_items[count($first_items) - 1]['time_start'];
                        $first_items[count($first_items) - 1]['time_end'] = $items[0]['time_start'];
                    }
                    $items = array_merge($first_items, $items);
                }


                for ($i = 0; $i < count($items); $i++) {
                    $item = $items[$i];
                    $items_with_new[] = $items[$i];
                    $break_length = null;
                    if ($i == count($items) - 1) {
                        $break_length = $time_end - $item['time_end'];
                    } elseif ($items[$i]['time_end'] != $items[$i + 1]['time_start']) {
                        $break_length = $items[$i + 1]['time_start'] - $items[$i]['time_end'];
                    } else {

                    }


                    if ($break_length) {
                        $added_items_length = 0;
                        $index = 0;
                        $playlist_items = $around_the_clock->extended_data->getItems();
                        $type = $around_the_clock->extended_data->playlistType;
                        while ($added_items_length < $break_length) {
                            if ($type === "default") {
                                if ($index >= count($playlist_items)) {
                                    $index = 0;
                                }
                                $current_item_data = $playlist_items[$index];
                                $index++;
                            } else {
                                $current_item_data = $playlist_items->random();
                            }

                            $current_item = [
                                'playlist_id' => $around_the_clock->id,
                                'data' => $current_item_data,
                                'length' => $current_item_data->length,
                                'time_start' => $items[$i]['time_start'] + $items[$i]['length'] + $added_items_length,
                                'time_end' => $items[$i]['time_start'] + $items[$i]['length'] + $added_items_length + $current_item_data->length
                            ];
                            if ($added_items_length + $current_item_data->length > $break_length) {
                                $current_item['trim_length'] = $current_item['length'] - ($break_length - $added_items_length);
                                $current_item['length'] = $break_length - $added_items_length;
                                $current_item['time_end'] = $items[$i]['time_start'] + $break_length + $current_item['length'];
                            }
                            if (!$real_first_item_time || $current_item['time_start'] >= $real_first_item_time) {
                                $items_with_new[] = $current_item;
                            }
                            $added_items_length += $current_item_data->length;
                        }
                    }
                }
            }
            $items = $items_with_new;
        }


        $list_without_duplicates = [];
        $last_item = null;
        $last_item_start_time = 0;
        $repeat_count = 0;

        $video_ids = [];

        foreach ($items as $item) {
            $video_ids[] = $item['data']->video_id;
        }
        $video_ids = array_unique($video_ids);

        $videos = Media::whereIn('id', $video_ids)->get();

        $video_data = [];
        foreach ($videos as $video) {
            $video_data[$video->id] = $video;
        }
        $prev_item = null;

        if (count($items) > 0) {
            for ($i = 0; $i <= count($items) ; $i++) {
                $item_id = isset($items[$i]) ? (isset($items[$i]['data']->inner_id) ? $items[$i]['data']->inner_id : $items[$i]['data']->id) : -1;
                $last_item_id = is_object($last_item['data']) ? (isset($last_item['data']->inner_id) ? $last_item['data']->inner_id : $last_item['data']->id) : null;
                if (isset($items[$i]) && $last_item && $item_id == $last_item_id) {
                    $repeat_count++;
                } else {
                    if ($last_item || !isset($items[$i])) {
                        if (isset($video_data[$last_item['data']->video_id])) {
                            $list_data = [
                               // 'is_atc' => isset($last_item['is_atc']) ? $last_item['is_atc'] : false,
                                'repeat_count' => $repeat_count,
                                'item_id' => $last_item_id,
                                'playlist_id' => (int)$last_item['playlist_id'],
                                'folder_id' => $last_item['data']->folder_id,
                                'time_start' => (int)$last_item['time_start'],
                                'time_end' => ((int)$last_item['time_end'] + ((int)$last_item['length'] * $repeat_count)),
                                'length' => (int)$last_item['length'],
                                'length_total' => ((int)$last_item['length'] * ($repeat_count + 1)),
                                'title' => $last_item['data']->title,
                                'data' => [
                                    'server' => $video_data[$last_item['data']->video_id]->server,
                                    'folder' => $video_data[$last_item['data']->video_id]->folder,
                                    'path' => $video_data[$last_item['data']->video_id]->url,
                                ]
                            ];

                            if ($prev_item && isset($prev_item['trim_length'])) {
                                if ($repeat_count > 0) {
                                    $list_data['time_end'] = $list_data['time_end'] - $prev_item['trim_length'];
                                    $list_data['length_total'] = $list_data['length_total'] - $prev_item['trim_length'];
                                    $list_data['data']['trim_length'] = $prev_item['trim_length'];
                                }
                            }
                            $list_without_duplicates[] = $list_data;
                        }
                        $repeat_count = 0;
                    }
                }
                if (isset($items[$i])) {
                    if ($repeat_count === 0) {
                        $last_item = $items[$i];
                    }
                    $prev_item = $items[$i];
                }
            }

            $index = $first_item_index;
            foreach ($list_without_duplicates as &$list_item) {
                $list_item['clip_index'] = $index;
                $list_item['channel_id'] = $id;
                $list_item['data'] = json_encode($list_item['data']);
                $index+= $list_item['repeat_count'] + 1;
            }

            if (isset($settings['verbose']) && $settings['verbose']) {
                echo count($list_without_duplicates). " inserted for channel id ".$id.PHP_EOL;
            }
            if (request()->has('test')) {
                foreach ($list_without_duplicates as &$item) {
                    echo $item['title']." ".($item['time_end'] - $item['time_start']). " / ".$item['length']."<br>";
                }
                dd('items', $list_without_duplicates);
            }
            $items_in_db = [];
            if (count($list_without_duplicates) > 7500) {
                echo "Too many items";
                return [];
            } else {
                AutopilotTempItem::insert($list_without_duplicates);
                $items_in_db = AutopilotTempItem::where(['channel_id' => $id])->orderBy('time_start', 'ASC')->get();
                Storage::disk('playlists_data')->put("v2_playlists_$id.json", json_encode($items_in_db, JSON_UNESCAPED_UNICODE));
            }
            return $items_in_db;
           // dd(AutopilotTempItem::where(['channel_id' => $id])->orderBy('time_start', 'ASC')->get(), $list_without_duplicates);

        }


    }
}
