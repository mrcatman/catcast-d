<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Support\Str;

class FederationController extends Controller {

    const CHANNELS_PREFIX = 'channel_';

    public function webfinger() {
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
                    'href' =>  $actor->web_url
                ]
            ]
        ]);
    }

}
