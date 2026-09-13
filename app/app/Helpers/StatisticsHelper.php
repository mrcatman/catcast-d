<?php

namespace App\Helpers;

use App\Models\StatisticsSession;

use Carbon\Carbon;
use GeoIp2\Database\Reader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StatisticsHelper {

    /**
     * Get hash from IP address and User-Agent to not store sensitive information in DB
     * @param string $ip
     * @param string $user_agent
     * @return string
     */
    public static function getDeviceHash($ip, $user_agent) {
        $id = $ip.'_'.$user_agent;
        return sha1($id);
    }

    /**
     * Get country code from an IP address using a GeoIP database (returns LOC if IP is local or not found)
     * @param string $ip
     * @return string
     */
    public static function getCountryCode($ip) {
        if ($ip == '127.0.0.1') {
            return 'LOC';
        }
        try {
            $reader = new Reader(storage_path(ConfigHelper::statisticsGeoIPDatabase()));
            $data = $reader->country($ip);
            return $data->country->isoCode;
        } catch (\Exception $e) {
            return 'LOC';
        }
    }

    /**
     * Increment views count for an entity
     * @param Model $entity
     * @void
     */
    public static function increment($entity) {
        $ip = request()->ip();
        $user_agent = request()->header('User-Agent'); // todo: make async
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
        $view = StatisticsSession::where($data)->orderBy('created_at', 'desc')->first();

        if (!$view || $view->created_at->lte(Carbon::now()->subSeconds($session_duration))) {
            $entity->views++;
            $entity->save();
            $view = new StatisticsSession($data);
            $view->save();
        } elseif ($view) {
            $view->updated_at = Carbon::now();
            $view->save();
        }
    }


    private static function count($query, $type_config) {
        return isset($type_config['sum_by']) ? $query->sum($type_config['sum_by']) : $query->count();
    }

    private static function getCountChart($base_query, $type, $type_config, $params) {
        $simultaneous = $type_config['simultaneous'] ?? false;
        if ($simultaneous) {
            $params['aggregate'] = false;
        }

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

        $data_limit_reached = false;
        $max_statistics_items_count = 10000; //todo: config

        $statistics_items_count = ceil(($params['end_time']->getTimestamp() - $params['start_time']->getTimestamp()) / $params['timespan']['duration']);
        if ($statistics_items_count > $max_statistics_items_count) {
            $data_limit_reached = true; // todo: show message on frontend
            $statistics_items_count = $max_statistics_items_count;
        }

        $total = 0;
        $time = $params['start_time']->clone();
        for ($i = 0; $i < $statistics_items_count; $i++) {
            $query = $simultaneous ?
                $base_query->clone()->groupBy('device_hash')->where('created_at', '<=', $time->clone()->addSeconds($params['timespan']['duration']))->where('updated_at', '>=', $time)
                : $base_query->clone()->where('created_at', '>=', $time)->where('created_at', '<', $time->clone()->addSeconds($params['timespan']['duration']));

            $value = self::count($query, $type_config);
            $total+= $value;

            if ($params['aggregate']) {
                $last_values[$type] += $value;
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

        $average = $statistics_items_count > 0 ? round($total / $statistics_items_count) : 0;
        return [
            'chart_type' => 'line',
            'chart_data' => $statistics,
            'numbers' => [
                [
                    'name' => 'statistics.total',
                    'value' => $total
                ],
                [
                    'name' => 'statistics.average',
                    'value' => $average
                ]
            ],
            'data_limit_reached' => $data_limit_reached
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

    public static function getTableByCountry($entity, $entity_type, $type, $type_config, $params)
    {
        $views_by_country = StatisticsSession::where(['entity_type' => $type_config['children_entity_type']])->whereIn('entity_id', $type_config['children_entity_ids'][$entity_type]($entity))
            ->where('created_at', '>', $params['start_time'])->where('updated_at', '<=', $params['end_time'])
            ->groupBy('country_code')
            ->select(['country_code', DB::raw('COUNT(*) as count')])
            ->orderBy('count', 'desc')->get();
        $total_views = $views_by_country->sum('count');

        $values = $views_by_country->map(function ($country_data) use ($total_views) {
            return [
                $country_data->country_code,
                $country_data->count,
                round($country_data->count / $total_views * 100).'%'
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
                        'statistics.views',
                        'statistics.percent'
                    ],
                    'values' => $values
                ]
            ]
        ];
    }



}
