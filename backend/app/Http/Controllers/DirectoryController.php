<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Models\Broadcast;
use App\Models\BroadcastCategory;
use App\Models\Channel;

class DirectoryController extends Controller {

    private $entrypoints = [];

    public function __construct() {
        $this->entrypoints = [
            [
                'id' => 'index',
                'entity' => 'directory',
                'children' => []
            ],
            [
                'id' => 'channels',
                'entity' => 'directory',
                'heading' => 'channels.heading',
                'children' => $this->channels()
            ],
            [
                'id' => 'subscriptions',
                'entity' => 'directory',
                'heading' => 'subscriptions.heading',
                'children' => $this->subscriptions()
            ],
            [
                'id' => 'media',
                'entity' => 'directory',
                'heading' => 'media.heading',
                'children' => $this->media()
            ]
        ];
    }

    public function menu() {
        return [
            [
                'heading' => 'global.explore',
                'children' => [
                    [
                        'id' => 'index',
                        'heading' => 'global.home',
                        'icon' => 'fa-home'
                    ],
                    [
                        'id' => 'subscriptions',
                        'heading' => 'global.subscriptions',
                        'icon' => 'subscriptions'
                    ]
                ]
            ],
            [
                'heading' => 'channels.heading',
                'children' => [
                    [
                        'id' => 'channels',
                        'heading' => 'global.all',
                        'icon' => 'live_tv'
                    ],
                    [
                        'id' => 'channels/tv',
                        'heading' => 'channels.tv',
                        'icon' => 'live_tv'
                    ],
                    [
                        'id' => 'channels/radio',
                        'heading' => 'channels.radio',
                        'icon' => 'fa-radio'
                    ]
                ]
            ],
            [
                'heading' => 'media.heading',
                'children' => [
                    [
                        'id' => 'media',
                        'heading' => 'global.all',
                        'icon' => 'play_arrow'
                    ],
                    [
                        'id' => 'media/video',
                        'heading' => 'media.video',
                        'icon' => 'play_arrow'
                    ],
                    [
                        'id' => 'media/audio',
                        'heading' => 'media.audio',
                        'icon' => 'play_arrow'
                    ]
                ]
            ]
        ];
    }

    public function index($path) {
        $path = explode('/', $path);
        $entrypoints = $this->entrypoints;
        $directory = null;
        foreach ($path as $id) {
            $directory = $entrypoints ? current(array_filter($entrypoints, function($item) use ($id) {
                return $item['id'] == $id;
            })) : null;
            if (!$directory) {
                return CommonResponses::notFound();
            }
            $entrypoints = $directory['children'] ?? null;
        }
        if (isset($directory['children'])) {
            foreach ($directory['children'] as &$child) {
                unset($child['children']);
            }
        }
        return $directory;
    }

    private function channels() {
        $online_broadcasts_exist = Broadcast::online()->count() > 0;
        return [
            [
                'id' => 'online',
                'entity' => 'channels',
                'heading' => 'channels.online',
                'params' => ['show' => 'online'],
            ],
            // todo: autopilot here,
            [
                'id' => 'categories',
                'entity' => 'categories',
                'heading' => 'categories.heading',
                'params' => ['show' => $online_broadcasts_exist ? 'popular_online' : 'popular'],
                'children' => [
                    [
                        'id' => 'new',
                        'entity' => 'categories',
                        'heading' => 'categories.new',
                        'params' => ['show' => 'new'],
                    ],
                    [
                        'id' => 'popular',
                        'entity' => 'categories',
                        'heading' => 'categories.popular',
                        'params' => ['show' => 'popular'],
                    ],
                    [
                        'id' => 'popular_online',
                        'entity' => 'categories',
                        'heading' => 'categories.popular_online',
                        'params' => ['show' => 'popular_online'],
                    ],
                ],
            ],
            [
                'id' => 'new',
                'entity' => 'channels',
                'heading' => 'channels.new',
                'params' => ['show' => 'new'],
            ],
            [
                'id' => 'popular',
                'entity' => 'channels',
                'heading' => 'channels.popular',
                'params' => ['show' => 'popular'],
            ],
            [
                'id' => 'tv',
                'entity' => 'channels',
                'heading' => 'channels.tv',
                'params' => ['type' => 'tv'],
                'hidden' => true
            ],
            [
                'id' => 'radio',
                'entity' => 'channels',
                'heading' => 'channels.radio',
                'params' => ['type' => 'radio'],
                'hidden' => true
            ],
        ];
    }

    private function subscriptions() {
        // todo: check sections availability
        return [
            [
                'id' => 'channels',
                'entity' => 'channels',
                'heading' => 'channels.subscriptions',
                'params' => ['show' => 'subscriptions'],
            ],
            [
                'id' => 'media',
                'entity' => 'media',
                'heading' => 'media.subscriptions',
                'params' => ['show' => 'subscriptions'],
            ]
        ];
    }

    private function media() {
        return [
            [
                'id' => 'new',
                'entity' => 'media',
                'heading' => 'media.new',
                'params' => ['show' => 'new'],
            ],
            [
                'id' => 'popular',
                'entity' => 'media',
                'heading' => 'media.popular',
                'params' => ['show' => 'popular'],
            ],
        ];
    }

    public function category($url) {
        $category = BroadcastCategory::where(['url' => $url])->orWhere(['id' => $url])->firstOrFail();
        return [
            'heading' => $category->name,
            'category' => $category,
            'children' => [
                [
                    'id' => 'channels',
                    'entity' => 'channels',
                    'heading' => 'channels.heading',
                    'params' => ['category_id' => $category->id],
                ],
                [
                    'id' => 'media',
                    'entity' => 'media',
                    'heading' => 'media.heading',
                    'params' => ['category_id' => $category->id],
                ],
            ]
        ];
    }


}

