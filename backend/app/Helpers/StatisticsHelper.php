<?php

namespace App\Helpers;

use App\Models\Channel;
use App\Models\Like;
use App\Models\RadioServer;
use App\Models\StatisticsSession;

use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\DB;

class StatisticsHelper {


    public static function getDeviceHash($ip, $user_agent) {
        $id = $ip.'_'.$user_agent;
        return sha1($id);
    }

    public static function getCountryCode($ip) {
        if ($ip == '127.0.0.1') {
            return 'LOCAL';
        }
        try {
            $reader = new Reader(storage_path(ConfigHelper::statisticsGeoIPDatabase()));
            $data = $reader->country($ip);
            return $data->country->isoCode;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function increment($entity, $ip = null) {
        if (!$ip) {
            $ip = request()->ip();
        }
        $user_agent = request()->header('User-Agent');
        $hash = self::getDeviceHash($ip, $user_agent);
        $data = [
            'device_hash' => $hash,
            'entity_type' => $entity->getEntityType(),
            'entity_id' => $entity->id
        ];
        if (ConfigHelper::statisticsStoreCountries()) {
            $data['country_code'] = self::getCountryCode($ip);
        }

        $session_duration = ConfigHelper::statisticsSessionDurationSeconds();

        $view = StatisticsSession::where('created_at', '>', time() - $session_duration)->firstOrNew($data);
        if (!$view->exists) {
            $entity->views++;
            $entity->save();
            $view->save();
        }
    }


    private static function count($query, $type_config) {
        return isset($type_config['sum_by']) ? $query->sum($type_config['sum_by']) : $query->count();
    }

    private static function getCountChart($base_query, $type, $type_config, $params) {
        if ($params['aggregate']) {
            $query = $base_query->clone()->where('created_at', '<', $params['start_time']);
            $last_values = [
                $type => self::count($query, $type_config)
            ];
            if (isset($type_config['additional_data'])) {
                foreach ($type_config['additional_data'] as $additional_type => $additional_config) {
                    $additional_query = $additional_config['where']($query->clone());
                    $last_values[$additional_type] = self::count($additional_query, $type_config);
                }
            }
        }

        $statistics = [
            $type => [
                'id' => $type,
                'name' => $type_config['name'],
                'values' => []
            ]
        ];


        $max_statistics_items_count = 100; //todo: config

        $statistics_items_count = floor(($params['end_time']->getTimestamp() - $params['start_time']->getTimestamp()) / $params['timespan']['duration']);
        if ($statistics_items_count > $max_statistics_items_count) {
            $statistics_items_count = $max_statistics_items_count;
        }

        $time = $params['start_time']->clone();
        for ($i = 0; $i < $statistics_items_count; $i++) {
            $query = $base_query->clone()->where('created_at', '>=', $time)->where('created_at', '<', $time->clone()->addSeconds($params['timespan']['duration']));

            $value = self::count($query, $type_config);
            if ($params['aggregate']) {
                $last_values[$type]+= $value;
                $value = $last_values[$type];
            }
            $statistics[$type]['values'][] = [
                'time' => $time->toISOString(),
                'value' => $value
            ];
            if (isset($type_config['additional_data'])) {
                foreach ($type_config['additional_data'] as $additional_type => $additional_config) {
                    $additional_query = $additional_config['where']($query->clone());
                    $additional_value = self::count($additional_query, $type_config);
                    if ($params['aggregate']) {
                        $last_values[$additional_type]+= $additional_value;
                        $additional_value = $last_values[$additional_type];
                    }
                    if (isset($additional_config['negative']) && $additional_config['negative']) {
                        $additional_value *= -1;
                    }
                    if (!isset($statistics[$additional_type])) {
                        $statistics[$additional_type] = [
                            'id' => $additional_type,
                            'name' => $additional_config['name'],
                            'color' => $additional_config['color'] ?? null,
                            'values' => []
                        ];
                    }
                    $statistics[$additional_type]['values'][] = [
                        'time' => $time->toISOString(),
                        'value' => $additional_value
                    ];
                }
            }
            $time->addSeconds($params['timespan']['duration']);
        }
        $statistics = array_values($statistics);
        return [
            'chart_type' => 'line',
            'chart_data' => $statistics
        ];
    }

    public static function getCountChartForEntity($entity, $entity_type, $type, $type_config, $params) {
        $base_query = $type_config['class']::where(['entity_type' => $entity_type, 'entity_id' => $entity->id]);
        return self::getCountChart($base_query, $type, $type_config, $params);
    }

    public static function getCountChartForList($entity, $entity_type, $type, $type_config, $params) {
        $base_query = $type_config['class']::where(['entity_type' => $type_config['children_entity_type']])->whereIn('entity_id', $type_config['children_entity_ids'][$entity_type]($entity));
        return self::getCountChart($base_query, $type, $type_config, $params);
    }

    public static function getViewsByCountryTable($entity, $entity_type, $type, $type_config, $params) {
        $views = StatisticsSession::where(['entity_type' => $entity_type, 'entity_id' => $entity->id])
            ->where('created_at', '>', $params['start_time'])->where('updated_at', '<=', $params['end_time'])
            ->groupBy('country_code')
            ->select(['country_code', DB::raw('COUNT(*) as count')])
            ->orderBy('count', 'desc')->get()->map(function($country_data) {
                return [
                    $country_data->country_code,
                    $country_data->count
                ];
            });
        return [
            'chart_type' => 'table',
            'chart_data' => [
                [
                    'id' => $type,
                    'name' => $type_config['name'],
                    'headings' => [
                        'statistics.country_name',
                        'statistics.views'
                    ],
                    'values' => $views
                ]
            ]
        ];
    }


    public static function updateOnlineViewers() {

        $views_data = [];
        $live = Channel::live()->get();
        foreach ($live as $channel) {
            $channel->loadLiveData();
            if ($channel->viewers > 0) {
                $views_data[] = [$channel->id, (int)$channel->viewers, time(), true, 0, $channel->progname];
            }

        };

        $live = Channel::autopilot()->get();
        foreach ($live as $channel) {
            $channel->loadAutopilotData();
            if ($channel->viewers > 0) {
                $views_data[] = [$channel->id, (int)$channel->viewers, time(), false, 0, $channel->program_name];
            }
        };

        $radio_servers = RadioServer::all();
        foreach ($radio_servers as $server) {
            $server_ip = $server->ip_address;
            $server_port = $server->port;
            $server_address = $server_ip;
            if ($server_port) {
                $server_address.= ':'.$server_port;
            }
            $listeners = file_get_contents("http://$server_address/status-json.xsl");
            $listeners = json_decode($listeners);
            if (isset($listeners->icestats)) {
                if (isset($listeners->icestats->source)) {
                    $source = $listeners->icestats->source;
                    if (is_array($source)) {
                        foreach ($source as $stream) {
                            $url_data = explode("/", $stream->listenurl);
                            $url_data = $url_data[count($url_data) - 1];
                            $channel_id = (int)explode(".", $url_data)[0];
                            $views_data[] = [$channel_id, (int)$stream->listeners, time(), false, 0, isset($source['title']) ? $source['title'] : ''];
                        }
                    } else {
                        $url_data = explode("/", $source->listenurl);
                        $url_data = $url_data[count($url_data) - 1];
                        $channel_id = (int)explode(".", $url_data)[0];
                        $views_data[] = [$channel_id, (int)$source->listeners, time(), false, 0, isset($source->title) ? $source->title : ''];
                    }
                }
            }
        }

        if (request()->filled('debug')) {
            var_dump($views_data);
        }
        $db = new \ClickHouseDB\Client(self::config());
        $db->database('default');
        //$db->write("CREATE TABLE default.statistics_online_channels (ChannelId UInt16, ViewersCount UInt16, IsLive UInt8, BroadcasterId UInt16, Date Date DEFAULT toDate(Time), Time DateTime, ProgramName String) ENGINE = MergeTree(Date, (ChannelId, IsLive, BroadcasterId, ProgramName), 8192)");

        $db->insert('statistics_online_channels',
            $views_data,
            ['ChannelId', 'ViewersCount', 'Time', 'IsLive', 'BroadcasterId', 'ProgramName']
        );
        echo count($views_data)." rows inserted into Clickhouse";
    }

    public static function getOnlineViewersData($channel_id, $start, $end) {
        $viewers_data = [];
        $start_date = date('Y-m-d H:i:s', $start);
        $end_date = date('Y-m-d H:i:s', $end);
        $db = new \ClickHouseDB\Client(self::config());
        $db->database('default');
        $select = "select ViewersCount, Time, ProgramName, IsLive, BroadcasterId from statistics_online_channels where Time>='$start_date' AND Time<='$end_date' AND ChannelId=$channel_id order by Time ASC";
        $data = $db->select($select);
        if (request()->filled('debug')) {

        }
        foreach ($data->rows() as $row) {
           $item = [
               'time' => $row['Time'],
               'count' => $row['ViewersCount'],
               'program_name' => $row['ProgramName'],
               'is_live' => (boolean)$row['IsLive']
           ];
           if ($row['IsLive']) {
               $item['broadcaster_id'] = $row['BroadcasterId'];
           }
           $viewers_data[] = $item;
        }
        return $viewers_data;
    }

    public function getLikesData($likes_module_id, $entity_id, $start_time, $end_time) {
        //$start_time = time() - 86400 * 365 * 3;
        //$end_time = time();
        $all_before = Like::where(['entity_type' => $likes_module_id, 'entity_id' => $entity_id])->where('created_at', '<=', $start_time)->get();
        $rating_before = 0;
        $likes_before = 0;
        $dislikes_before = 0;
        foreach ($all_before as $like) {
            $rating_before += $like->weight;
            if ($like->weight > 0) {
                $likes_before+= $like->weight;
            } else {
                $dislikes_before += -1 * $like->weight;
            }
        }

        $items_in_interval = Like::where(['entity_type' => $likes_module_id, 'entity_id' => $entity_id])->where('created_at', '>', $start_time)->where('created_at', '<', $end_time)->get();

        $rating_total_data = [];
        $likes_total_data = [];
        $dislikes_total_data = [];

        $likes_by_day_data = [];
        $dislikes_by_day_data = [];
        $rating_by_day_data = [];
        $days = (int)ceil(($end_time - $start_time) / 86400);
        for ($i = 0; $i <= $days; $i++) {
            $date_ts = $start_time + $i*86400;
            $date = date('d.m.Y', $date_ts);

            $items_before = $items_in_interval->filter(function($like) use ($date_ts) {
                return $like->addtime <= $date_ts;
            });

            $rating_total_data[$date] = $rating_before + $items_before->sum('weight');
             $likes_total_data[$date] = $likes_before + $items_before->filter(function($like) {
                return $like->weight > 0;
            })->sum('weight');
            $dislikes_total_data[$date] = $dislikes_before + -1 * $items_before->filter(function($like) {
                return $like->weight < 0;
            })->sum('weight');

            $likes_by_day_data[$date] = 0;
            $dislikes_by_day_data[$date] = 0;
            $rating_by_day_data[$date] = 0;
        }
        foreach ($items_in_interval as $like) {
            $date = date('d.m.Y', $like->addtime);
            $rating_by_day_data[$date] += $like->weight;
            if ($like->weight > 0) {
                $likes_by_day_data[$date] += $like->weight;
            } else {
                $dislikes_by_day_data[$date] += -1 * $like->weight;
            }
        }

        $all_items =  Like::where(['entity_type' => $likes_module_id, 'entity_id' => $entity_id])->get();
        $all_rating = $all_items->sum('weight');
        $all_likes = $all_items->filter(function($like) {
            return $like->weight > 0;
        })->sum('weight');
        $all_dislikes = -1 * $all_items->filter(function($like) {
            return $like->weight < 0;
        })->sum('weight');
        $all_total = count($all_items);
        return [
            'by_day' => [
                'rating' => $rating_by_day_data,
                'likes' => $likes_by_day_data,
                'dislikes' => $dislikes_by_day_data
            ],
            'total' => [
                'rating' => $rating_total_data,
                'likes' => $likes_total_data,
                'dislikes' => $dislikes_total_data
            ],
            'now' => [
                'rating' => $all_rating,
                'likes' => $all_likes,
                'dislikes' => $all_dislikes,
                'likes_percent' => $all_total > 0 ? $all_likes / $all_total * 100 : 0,
                'dislikes_percent' => $all_total > 0 ?  $all_dislikes / $all_total * 100 : 0
            ]
        ];
    }
}
