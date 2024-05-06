<?php

namespace App\Http\Controllers;


use App\Autopilot\AutopilotFolder;
use App\Autopilot\AutopilotItem;
use App\Autopilot\AutopilotPlaylist;
use App\Autopilot\AutopilotRepository;
use App\Autopilot\AutopilotTempItem;
use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Models\Announce;
use App\Models\Channel;
use App\Helpers\Array2XML;
use App\Models\Media;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class TimetableController extends Controller {

    protected $subscriptionTypes = [
        'announce', 'autopilot_item', 'autopilot_folder', 'autopilot_playlist'
    ];

    public function getAllByChannel($channel_id) {
        $channel = Channel::findOrFail($channel_id);
        $has_autopilot_permissions = PermissionsHelper::getStatus(['autopilot'], $channel);
        $has_broadcast_permissions = PermissionsHelper::getStatus(['live_broadcast'], $channel);
        if ($has_autopilot_permissions || $has_broadcast_permissions) {
            $user = auth()->user();
            $has_full_autopilot_permissions =PermissionsHelper::getStatus(['autopilot'], $channel);
            $has_full_broadcast_permissions = $this->checkFullPermissions($channel_id, ['live_broadcast']);
            $autopilot = AutopilotRepository::getForChannelId($channel_id)->getPlaylists();

            $video_ids = [];
            foreach ($autopilot as &$playlist) {
                $playlist->can_edit = ($has_autopilot_permissions && $playlist->user_id === $user->id) || $has_full_autopilot_permissions;
                foreach ($playlist->items as $item) {
                    $video_ids[] = $item->video_id;
                }
            };
            $videos = Media::whereIn('id', $video_ids)->get();
            foreach ($videos as $video) {
                $GLOBALS['video_data_'.$video->id] = $video;
            }


          //  $ids = [];
            $announces = Announce::where(['channelid' => $channel_id])->get();

            $announces->each(function($announce) use ($user, $has_broadcast_permissions, $has_full_broadcast_permissions) {
                $announce->can_edit = ($has_broadcast_permissions && $announce->user_id === $user->id) || $has_full_broadcast_permissions;
            });

            return [
               'playlists' => $autopilot,
                'announces' => $announces,
            ];
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function getForChannel($channel_id) {
        $channel = Channel::findOrFail($channel_id);
        if (!$channel->show_timetable) {
            return CommonResponses::unauthorized();
        }
        $collection = AutopilotRepository::getForChannelId($channel_id);
        $time = time();
        $day = request()->input('day', date('d.m.Y', $time));
        $day_start = strtotime($day. ' 00:00');
        $day_end = $day_start + 86400;
        $autopilot = $collection->getForTimeInterval($day_start, $day_end);
        $announces = Announce::where(['channelid' => $channel_id])->where('created_at','>=', $day_start)->where('created_at', '<=', $day_end)->orderBy('created_at')->get();
        $items = collect($autopilot)->sortBy('time');
        $items = $items->merge($announces);
        $items = $items->transform(function($item) {
            return $this->getReadableView($item);
        });
        $items = $items->sortBy('time')->values();

        return $items;
    }


    public function getInOldFormat($channel_id) {
        $channel = Channel::find($channel_id);
        if (!$channel) {
            return [];
        }
        if (!$channel->show_timetable) {
            return [];
        }
        //$collection = AutopilotRepository::getForChannelId($channel_id);
        $time = time();
        $day = request()->input('date', date('d.m.Y', $time));
        $day_start = strtotime($day. ' 00:00');
        if (request()->has('offset')) {
            $offset = (int)request()->input('offset');
            $day_start+= $offset * 60;
        }
        $day_end = $day_start + 86400;
     //   $autopilot = $collection->getForTimeInterval($day_start, $day_end);
        $autopilot = null;
       // if (count($autopilot) === 0) {
            $temp_items = AutopilotTempItem::where(['channel_id' => $channel_id])->where('time_start', '>=', $day_start)->get(); //->where('time_end', '<=', $day_end)
            foreach ($temp_items as $temp_item) {
               // $item = $temp_item->getItem();
               // $item->channel_id= $channel_id;
                $time_start = $temp_item->time_start;
                if (request()->has('offset')) {
                    $offset = (int)request()->input('offset');
                    $time_start+= $offset * 60;
                }
                $picture = $temp_item->getItem()->picture;
                $item = (object)[
                    '_time' => $time_start,
                    'iso_start' => date('c', $time_start),
                    'iso_end' => date('c', $time_start + $temp_item->length),
                    'date' => date('d.m.Y H:i', $time_start),
                    'picture' => $picture,
                    'length' => $temp_item->length,
                    'time' => date('H:i', $time_start),
                    'title' => $temp_item->title,
                ];
                if ($temp_item->time_start < time() && $temp_item->time_end > time()) {
                    $item->now = true;
                }
                $autopilot[] = $item;
            }
       // } else {

       // }
        $announces = Announce::where(['channelid' => $channel_id])->where('created_at','>=', $day_start)->where('created_at', '<=', $day_end)->orderBy('created_at')->get();
        $items = collect($autopilot)->sortBy('time');
        $items = $items->merge($announces);
        $items = $items->sortBy('_time')->values();
        foreach ($items as &$item) {
            unset($item->_time);
        }
        return $items;
    }

    public function index() {
        $only_live = (bool)request()->input('only_live', false) === true;
        $filter = request()->input('filter', '');
        $time = request()->input('time', time());
        $search = request()->input('search', '');
        $min_length = 250;
        if (!$only_live) {
            $repository = new AutopilotRepository();
            $only_liked = $filter == "liked";
            $next = $repository->getNext($time, $min_length, $only_liked);
        }
        $announces = Announce::where('created_at','>=', $time)->orderBy('created_at');
        if ($filter == "liked") {
            $announces = $announces->fromLikedChannels();
        }
        $announces = $announces->get();

        if (!$only_live) {
            $next = collect($next)->merge($announces); //-;
        } else {
            $next = $announces;
        }
        $next = $next->sortBy('time');
        $count = request()->input('count', 10);
        $page = request()->input('page', 1);

        $page_items = $next->slice(($page - 1) * $count, $count);
        $items = [];
        foreach ($page_items as $item) {
            $items[] = $this->getReadableView($item, true);
        }

        $paginator = new LengthAwarePaginator(
            $items,
            $next->count(),
            $count,
            $page
        );
        return $paginator;
    }

    public function getNextByChannel($channel_id) {
        $only_live = (bool)request()->input('only_live', false) === true;
        $time = request()->input('time', time());
        $search = request()->input('search', '');
        $min_length = 250;
        $next = [];
        if (!$only_live) {
            $repository = new AutopilotRepository();
            $next = $repository->getNextForChannel($channel_id, $time, $min_length);
        }
        foreach ($next as $item) {
            if (!$item->description && $item->video && $item->video->description != "") {
                $item->description = $item->video->description;
            }
        }
        $announces = Announce::where('created_at','>=', $time)->where(['channelid' => $channel_id])->orderBy('created_at');
        $announces = $announces->get();
        if (!$only_live) {
            $next = collect($next)->merge($announces); //-;
        } else {
            $next = $announces;
        }
        $next = $next->sortBy('time');
        $count = request()->input('count', 10);
        $page = request()->input('page', 1);

        $page_items = $next->slice(($page - 1) * $count, $count);
        $items = [];
        foreach ($page_items as $item) {
            $items[] = $this->getReadableView($item, false);
        }

        $paginator = new LengthAwarePaginator(
            $items,
            $next->count(),
            $count,
            $page
        );
        return $paginator;
    }

    public function getReadableView($item, $with_channel = false) {
        if ($item instanceof Announce) {
            $data = (object)[
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'time' => $item->time,
                'readable_time' => (abs($item->time - time() ) > 86400) ? date('d.m.Y H:i', $item->time) : date('H:i', $item->time),
                'picture' => $item->cover,
                'length' => $item->length * 60,
                'live' => true,
            ];
            if ($with_channel) {
                $data->channel = $item->channel;
            }
        } else {
            $data = $item;
            if (isset($data->description)) {
                $data->description = strip_tags($data->description);
            }
            if ($with_channel) {
                if (isset($data->channel_id)) {
                    $data->channel = Channel::find($data->channel_id);
                } else {
                    $data->load('playlist.channel');
                    $channel = $data->playlist->channel;
                    unset($data->playlist);
                    $data->channel = $channel;
                }
            }

        }
        if (isset($item->type)) {
            $type = $item->type;
        } else {
            $type = get_class($item);
            $type = explode("\\", $type);
            $type = $type[count($type) - 1];
            $type = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $type));
        }
        $data->item_type = $type;
        $data->is_live = $item instanceof Announce;

        return $data;
    }

    public function getSubscriptions($type, $id) {
        if (in_array($type, $this->subscriptionTypes)) {
            $subscriptions = ProgramNotification::where(['program_type' => $type, 'program_id' => $id])->get();
            $count = count($subscriptions);
            $is_subscribed = false;
            if ($user = auth()->user()) {
                $subscription = $subscriptions->first(function($subscription) use ($user) {
                    return $subscription->user_id === $user->id;
                });
                if ($subscription) {
                    if ($subscription->is_in_repeating_playlist) {
                        $day = request()->input('day', date('d.m.Y', time()));
                        $day_start = strtotime($day. ' 00:00');
                        $is_subscribed = (bool)$subscriptions->first(function($subscription) use ($user, $day_start) {
                            $time = strtotime($subscription->time);
                            return $subscription->user_id === $user->id && $time >= $day_start && $time <= $day_start + 86400;
                        });
                        $count = count($subscriptions->filter(function($subscription) use ($day_start) {
                            $time = strtotime($subscription->time);
                            return $time >= $day_start && $time <= $day_start + 86400;
                        }));
                    } else {
                        $is_subscribed = true;
                    }
                } else {
                    $is_subscribed = false;
                }
            }

            return [
                'count' => $count,
                'is_subscribed' => $is_subscribed
            ];
        } else {
            return response()->json(['message' => 'timetable.subscription.errors.wrong_type'], 422);
        }
    }

    public function setSubscription($type, $id)
    {
        if (in_array($type, $this->subscriptionTypes)) {
            $user = auth()->user();
            $is_in_repeating_playlist = false;
            $day = request()->input('day', date('d.m.Y', time()));
            $state = (bool)request()->input('state', true);
            $has_item = false;
            $channel_id = null;
            $time = null;
            switch ($type) {
                case 'autopilot_item':
                    $item = AutopilotItem::find($id);
                    if ($item) {
                        $playlist = $item->playlist;
                        if ($playlist) {
                            $has_item = true;
                            $channel_id = $playlist->channel_id;
                            $time = $playlist->extended_data->getItemTime($id, $day);
                            if ($playlist->extended_data->playbackType === "repeating") {
                                $is_in_repeating_playlist = true;
                            }
                        }
                    }
                    break;
                case 'autopilot_folder':
                    $folder = AutopilotFolder::find($id);
                    if ($folder) {
                        $playlist = $folder->playlist;
                        if ($playlist) {
                            $has_item = true;
                            $channel_id = $playlist->channel_id;
                            $time = $playlist->extended_data->getItemTime($id, true);
                            if ($playlist->extended_data->playbackType === "repeating") {
                                $is_in_repeating_playlist = true;
                            }
                        }
                    }
                    break;
                case 'autopilot_playlist':
                    $playlist = AutopilotPlaylist::find($id);
                    if ($playlist) {
                        $has_item = true;
                        $channel_id = $playlist->channel_id;
                        $time = $playlist->extended_data->getPlaylistTime($day);
                        if ($playlist->extended_data->playbackType === "repeating") {
                            $is_in_repeating_playlist = true;
                        }
                    }
                    break;
                case 'announce':
                    $announce = Announce::find($id);
                    if ($announce) {
                        $has_item = true;
                        $channel_id = $announce->channel_id;
                        $time = $announce->time;
                    }
                    break;
                default:
                    break;
            }
            $subscription = ProgramNotification::where(['program_type' => $type, 'program_id' => $id, 'user_id' => $user->id]);
            if ($is_in_repeating_playlist) {
                $day_start = strtotime($day . ' 00:00');
                $subscription = $subscription->whereDate('time', '=', date('Y-m-d', $day_start));
            };
            $subscription = $subscription->first();
            if (!$subscription && $state) {
                $subscription = new ProgramNotification();
                if ($has_item) {
                    $subscription->is_in_repeating_playlist = $is_in_repeating_playlist;
                    $subscription->time = Carbon::createFromTimestamp($time);
                    $subscription->user_id = $user->id;
                    $subscription->channel_id = $channel_id;
                    $subscription->program_type = $type;
                    $subscription->program_id = $id;
                    $subscription->is_sent = false;
                    $subscription->save();
                } else {
                    return [
                        'status' => 0,
                        'text' => 'errors.item_not_found'
                    ];
                }
            } elseif ($subscription && !$state) {
                $subscription->delete();
            }
            $subscriptions = ProgramNotification::where(['program_type' => $type, 'program_id' => $id]);
            if ($is_in_repeating_playlist) {
                $day_start = strtotime($day . ' 00:00');
                $subscriptions = $subscriptions->whereDate('time', '=', date('Y-m-d', $day_start));
            };
            $subscriptions = $subscriptions->get();
            $count = count($subscriptions);
            return [
                'new_count' => $count,
                'is_subscribed' => $state
            ];
        } else {
            return response()->json(['message' => 'timetable.subscription.errors.wrong_type'], 422);
        }
    }

    public function getXmlEpg() {
        date_default_timezone_set("Europe/Moscow");
        $ids = explode(",", request()->input('channel_ids', ''));
        $channels = Channel::whereIn('id', $ids)->get();
        if (count($channels) === 0) {
            return;
        }
        $url = Config::get('app.client_host');
        $api_url = Config::get('app.url');
        $tv = [
            '@attributes' => [
                'source-info-url' => $url,
                'source-info-name' => 'Catcast.tv EPG',
                'generator-info-url' =>  $api_url
            ],
            'channel' => [],
            'programme' => []
        ];
        foreach ($channels as $channel) {
            $tv['channel'][] = [
                '@attributes' => [
                    'id' => $channel->id,
                ],
                'display-name' => [
                    '@attributes' => [
                        'lang' => 'ru'
                    ],
                    '@value' => $channel->name
                ],
                'icon' => [
                    '@attributes' => [
                        'src' => $channel->logo
                    ]
                ]
            ];
            if ($channel->show_timetable) {
                if ($channel->id == 33418) {
                    $items = AutopilotTempItem::where(['channel_id' => 33418])->orderBy('time_start')->get();
                    foreach ($items as $item) {
                        $tv['programme'][] = [
                            '@attributes' => [
                                'start' => date('Ymdhis', $item->time_start)." +0000",
                                'stop' => date('Ymdhis', $item->time_start + $item->length_total)." +0000",
                                'channel' => $channel->id
                            ],
                            'title' => [
                                '@attributes' => [
                                    'lang' => 'ru'
                                ],
                                '@value' => $item->title
                            ],
                            //'desc' => [
                            //    '@attributes' => [
                            //        'lang' => 'ru'
                            //    ],
                            //    '@value' => $item->description ? $item->description : ''
                            //]
                        ];
                    }
                } else {
                    $collection = AutopilotRepository::getForChannelId($channel->id);

                    $start = time() - 86400 * 1;
                    $end = time() + 86400 * 4;
                    $autopilot = $collection->getForTimeInterval($start, $end);

                    $announces = Announce::where(['channelid' => $channel->id])->where('created_at', '>=', $start)->where('created_at', '<=', $end)->orderBy('created_at')->get();
                    $items = collect($autopilot)->sortBy('time');
                    $items = $items->merge($announces);

                    $items = $items->transform(function ($item) {
                        return $this->getReadableView($item);
                    });
                    $items = $items->sortBy('time')->values();
                    $offset = request()->input('offset', 0);
                    $offset = $offset * 3600;
                    $ts = !!request()->input('ts');
                    if (count($items) === 0) {
                        $items = AutopilotTempItem::where(['channel_id' => $channel->id])->get();
                        foreach ($items as $item) {
                            $item->time = $item->time_start;
                            $item->length = $item->length_total;
                        }
                    }
                    $videos = Media::whereIn('id', $items->pluck('video_id'))->get();
                    $videos_list = [];
                    foreach ($videos as $video) {
                        $videos_list[$video->id] = $video;
                    }
                    foreach ($items as $item) {
                        $tv['programme'][] = [
                            '@attributes' => [
                                'start' => $ts ? ($item->time + $offset) * 1000 :  date('YmdHis', $item->time + $offset) . " +0300",
                                'stop' => $ts ? ($item->time + $item->length + $offset) * 1000 : date('YmdHis', $item->time + $item->length + $offset) . " +0300",
                                'channel' => $channel->id
                            ],
                            'title' => [
                                '@attributes' => [
                                    'lang' => 'ru'
                                ],
                                '@value' => $item->title
                            ],
                            'desc' => [
                                '@attributes' => [
                                    'lang' => 'ru'
                                ],
                                '@value' => isset($item->video_id) ? (isset($videos_list[$item->video_id]) ? strip_tags(str_replace('<br>', '\n', $videos_list[$item->video_id]->description)) : '') : ''
                            ]
                        ];
                    }
                }
            }
        }
        header("Content-Type: application/xml; charset=utf-8");
        $xml = Array2XML::createXML('tv', $tv);
        $text =  $xml->saveHTML( $xml->documentElement );
        echo '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE tv SYSTEM "'.$api_url.'/public/xmltv.dtd">';
        echo $text;
    }

}
