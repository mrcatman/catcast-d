<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Media;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FederationController extends Controller
{

    const CHANNELS_PREFIX = 'channel_';

    public function webfinger()
    {
        if (!request()->filled('resource')) {
            abort(400);
        }
        $resource = str_replace('acct:', '', request()->input('resource'));
        $resource = explode('@', $resource);
        if (count($resource) !== 2) {
            abort(400);
        }
        if ($resource[1] !== config('site.domains.web')) {
            abort(400);
        }
        $actor = null;
        $aliases = [];
        if (Str::startsWith($resource[0], self::CHANNELS_PREFIX)) {
            $actor = Channel::where(['shortname' => str_replace(self::CHANNELS_PREFIX, '', $resource[0])])->first();
        } else {
            $actor = User::where(['username' => $resource[0]])->first();
        }
        if (!$actor) {
            abort(404);
        }

        return response()->json([
            'subject' => request()->input('resource'),
            'aliases' => [
                $actor->web_url
            ],
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $actor->actor_url
                ],
                [
                    'rel' => 'http://webfinger.net/rel/profile-page',
                    'type' => 'text/html',
                    'href' => $actor->web_url
                ]
            ]
        ]);
    }

    public function nodeinfo()
    {
        return [
            "version" => "2.0",
            "metadata" => [
                "nodeName" => config('site.appearance.site_name'),
                "nodeDescription" => config('site.welcome.description'),
                "administrators" => User::where(['role_id' => User::ROLE_ID_ADMIN])->select('id', 'username')->get()->values()
            ],
            "protocols" => [], //todo: add activitypub when it'll be ready
            "services" => ["inbound" => [], "outbound" => []],
            "software" => [
                "name" => "catcast",
                "version" => "0.0.0" // todo: change
            ],
            "openRegistrations" => config('site.users.registration_enabled') && config('site.users.registration_open'),
            "manualRegistrations" => !config('site.users.registration_open'),
            "usage" => [
                "localPosts" => 0,
                "users" => [
                    "total" => User::whereNull('domain')->count(),
                    "activeHalfyear" => User::whereNull('domain')->whereDate('last_seen', '>=', Carbon::now()->subMonths(6))->count(),
                    "activeMonth" => User::whereNull('domain')->whereDate('last_seen', '>=', Carbon::now()->subMonth())->count(),
                ],
                "channels" => [
                    "total" => Channel::whereNull('domain')->count(),
                    "activeHalfyear" => Channel::whereNull('domain')->whereDate('last_online_at', '>=', Carbon::now()->subMonths(6))->count(),
                    "activeMonth" => Channel::whereNull('domain')->whereDate('last_online_at', '>=', Carbon::now()->subMonth())->count(),
                ],
                "media" => [
                    "total" => Media::count(),
                ]
            ]
        ];
    }

}
