<?php

namespace App\Http\Controllers;

use App\Autopilot\AutopilotRepository;
use App\Autopilot\AutopilotTempItem;
use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Playlist;
use App\Helpers\StatisticsHelper;

use App\Models\Media;
use App\Models\StatisticsSession;
use Carbon\Carbon;


class StatisticsController extends Controller {

    private $classes = [];

    private $types = [];

    public function __construct() {
        $this->classes = [
            Channel::getEntityType() => Channel::class,
            Playlist::getEntityType() => Playlist::class,
            Media::getEntityType() => Media::class,
        ];

        $this->types = [
            'views' => [
                'name' => 'statistics.views',
                'class' => StatisticsSession::class,
                'entity_types' => [Media::getEntityType()],
                'handler' => 'getCountChartForEntity',
            ],
            'media_views' => [
                'name' => 'statistics.media_views',
                'class' => StatisticsSession::class,
                'entity_types' => [Channel::getEntityType(), Playlist::getEntityType()],
                'handler' => 'getCountChartForList',
                'children_entity_type' => Media::getEntityType(),
                'children_entity_ids' => [
                    Channel::getEntityType() => function(Channel $channel) {
                        return $channel->media()->pluck('media.id');
                    },
                    Playlist::getEntityType() => function(Playlist $playlist) {
                        return $playlist->media()->pluck('media.id');
                    },
                ]
            ],
            'rating' => [
                'name' => 'statistics.rating',
                'class' => Like::class,
                'entity_types' => [Media::getEntityType()],
                'handler' => 'getCountChartForEntity',
                'sum_by' => 'weight',
                'additional_data' => [
                    'likes' => [
                        'name' => 'statistics.likes',
                        'where' => function($q) {
                            return $q->where('weight', '>', '0');
                        },
                        'color' => '--positive-color',
                    ],
                    'dislikes' => [
                        'name' => 'statistics.dislikes',
                        'where' => function($q) {
                            return $q->where('weight', '<', '0');
                        },
                        'color' => '--negative-color',
                        'negative' => true
                    ]
                ]
            ],
            'subscribers' => [
                'name' => 'statistics.subscribers',
                'class' => Like::class,
                'handler' => 'getCountChartForEntity',
                'entity_types' => [Channel::getEntityType(), Playlist::getEntityType()],
            ],
            'comments' => [
                'name' => 'statistics.comments',
                'class' => Comment::class, // todo: aggregate child comments,
                'handler' => 'getCountChartForEntity',
                'entity_types' => [Channel::getEntityType(), Playlist::getEntityType(), Media::getEntityType()]
            ],
            'countries' => [
                'name' => 'statistics.countries',
                'handler' => 'getViewsByCountryTable',
                'entity_types' => [Channel::getEntityType(), Playlist::getEntityType(), Media::getEntityType()]
            ]
        ];
    }


    private $timespans = [
        'hour' => [
            'name' => 'statistics.timespans.hour',
            'duration' => 3600
        ],
        'day' => [
            'name' => 'statistics.timespans.day',
            'duration' => 86400
        ],
    ];

    private function checkStatisticsPermissions($entity) {
        if ($entity::getEntityType() == Channel::getEntityType()) {
            return PermissionsHelper::getStatus(['statistics'], $entity);
        } else {
            return PermissionsHelper::getStatus(['statistics'], $entity->channel);
        }
    }

    private function getTypes($entity_type) {
        $options = [];
        foreach ($this->types as $key => $type) {
            if (in_array($entity_type, $type['entity_types'])) {
                $options[] = [
                    'id' => $key,
                    'name' => $type['name']
                ];
            }
        }
        return $options;
    }

    private function getTimespans() {
        $options = [];
        foreach ($this->timespans as $key => $timespan) {
            $options[] = [
                'id' => $key,
                'name' => $timespan['name']
            ];
        }
        return $options;
    }



    public function getConfig($entity_type) {
        return [
            'types' => $this->getTypes($entity_type),
            'timespans' => $this->getTimespans()
        ];
    }



    public function get($entity_type, $entity_id, $type) {
        if (!isset($this->classes[$entity_type])) {
            return CommonResponses::notFound();
        }
        $entity = $this->classes[$entity_type]::findOrFail($entity_id);
        if (!$this->checkStatisticsPermissions($entity)) {
            return CommonResponses::unauthorized();
        }

        $start_time = Carbon::parse(request()->input('start_time', time() - 7 * 86400));
        $end_time = Carbon::parse(request()->input('end_time', time()));
        $aggregate = request()->input('aggregate') == 'true';


        if (!in_array($type, array_keys($this->types))) {
            return CommonResponses::validationError(['types' => 'global.incorrect_value']);
        }
        $type_config = $this->types[$type];

        $timespan = request()->input('timespan', 'day');
        if (!in_array($timespan, array_keys($this->timespans))) {
            return CommonResponses::validationError(['timespan' => 'global.incorrect_value']);
        }
        $timespan_config = $this->timespans[$timespan];

        $params = [
            'start_time' => $start_time,
            'end_time' => $end_time,
            'aggregate' => $aggregate,
            'timespan' => $timespan_config
        ];
        return StatisticsHelper::{$type_config['handler']}($entity, $entity_type, $type, $type_config, $params);
    }


    public function getRealtimeChannelViewers($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['statistics']);

        $is_online = AutopilotTempItem::where(['channel_id' => $channel_id])->where('time_start', '<=', time())->where('time_end','>=',time())->count() > 0;
        //$viewers_list = [];
        $list_by_country = [];
        if ($channel->is_radio) {
            $viewers_list = []; //RadioAPI::getViewersList($channel_id);
        } else {
            if ($is_online) {
                $viewers_list = AutopilotRepository::getViewersList($channel_id);
            } else {
                $viewers_list = BroadcastServers::getViewersList($channel);
            }
        }

        foreach ($viewers_list as $viewer) {
            try {
                $data = $reader->country($viewer);
                if (!isset($list_by_country[$data->country->isoCode])) {
                    $list_by_country[$data->country->isoCode] = [
                        'data' => $data->country,
                        'code' => $data->country->isoCode,
                        'count' => 0,
                        'ips' => [],
                    ];
                }
                $list_by_country[$data->country->isoCode]['ips'][] = $viewer;
                $list_by_country[$data->country->isoCode]['count']++;
            } catch (\GeoIp2\Exception\AddressNotFoundException $e) {
                if (!isset($list_by_country['unknown'])) {
                    $list_by_country['unknown'] = [
                        'data' => [],
                        'code' => ["unknown"],
                        'count' => 0,
                        'ips' => [],
                    ];
                }
                $list_by_country["unknown"]['ips'][] = $viewer;
                $list_by_country["unknown"]['count']++;
            }
        }
        $list_by_country = collect($list_by_country);
        $list_by_country = $list_by_country->sortByDesc('count')->values();
        return [
            'total' => count($viewers_list),
            'list' => $list_by_country
        ];
    }

    public function getOnlineViewersHistory($channel_id) {
        $channel = PermissionsHelper::getChannelIfAllowed($channel_id, ['statistics']);
        $start_time = request()->input('start_time', time() - 7 * 86400);
        $end_time = request()->input('end_time', time());

        $statistics_module = new StatisticsHelper();
        $online_data = $statistics_module->getOnlineViewersData($channel_id, $start_time, $end_time);

        return $online_data;
    }
}
