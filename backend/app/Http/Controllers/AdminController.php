<?php

namespace App\Http\Controllers;


use App\Helpers\RadioAPI;
use App\Helpers\VideoServerAPI;
use App\Models\Audio;
use App\Models\BillingRecharge;
use App\Models\Donate;
use App\Models\DonateRequest;
use App\Models\Channel;

use App\Models\Picture;
use App\Notifications\ChannelNewFeedPost;
use App\Models\User;
use App\Models\Media;
use App\Rules\PictureId;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class AdminController extends Controller{


    public function channels() {
        $channels = Channel::orderBy('id', 'desc');
        if (request()->filled('name')) {
            $name = request()->input('name');
            $channels->where(function ($query) use ($name) {
                $query->where('name', 'LIKE', '%' . $name . '%');
                $query->orWhere('shortname', 'LIKE', '%' . $name . '%');
            });
        }
        $channels = $channels->paginate(request()->input('count', 30));
        $channels->getCollection()->transform(function ($channel) {
            $channel->space_occupied = $channel->is_radio ? Audio::where(['channel_id' => $channel->id])->sum('file_size') : Media::where(['channelid' => $channel->id])->sum('file_size');
            return $channel;
        });
        return $channels;
    }

    public function changeBanStatus() {
        $channel = Channel::findOrFail(request()->input('channel_id'));
        if (request()->input('status')) {
            $channel->blocked_at = Date::now();
            $channel->block_reason = request()->input('reason');
            $videos = Media::where(['channelid' => $channel->id])->get();
            foreach ($videos as $video) {
                $response = VideoServerAPI::request('delete_file', [
                    'url' => $video->url,
                    'channel_id' => $video->channel_id
                ], $video->server);
                $video->delete();
            }
        } else {
            $channel->blocked_at = null;
            $channel->block_reason = null;
        }
        $channel->save();
        return ['message' => 'global.saved'];

    }


    public function users() {
        $users = User::where('username', '!=', '');
        if (request()->filled('name')) {
            $name = request()->input('name');
            $users->where(function ($query) use ($name) {
                $query->where('username', 'LIKE', '%' . $name . '%');
                $query->orWhere('name', 'LIKE', '%' . $name . '%');
            });
        }
        if (request()->filled('sort')) {
            $order = request()->input('order', 'ASC');
            $sort = request()->input('sort', 'id');
            $users = $users->orderBy($sort, $order);
        }
        $users = $users->paginate(request()->input('count', 30));
        $users->getCollection()->transform(function ($user) {
            $user->channels_count = Channel::where(['user_id' => $user->id])->count();
            $user->channels_names = implode(", ", Channel::where(['user_id' => $user->id])->pluck('name')->toArray());
            $user->balance = $user->getBalance();
            return $user;
        });
        return $users;

    }

    public function topUpBalance() {
        $user_to_change = User::findOrFail(request()->input('user_id'));
        $recharge = new BillingRecharge();
        $recharge->user_id = $user_to_change->id;
        $recharge->sum = request()->input('sum');
        $recharge->status = 1;
        $recharge->provider = "manual";
        $recharge->save();
        return [
            'message' => 'global.saved',
            'new_balance' => $user_to_change->getBalance()
        ];
    }

    public function donateRequests() {
        $requests = Donate::paginate(request()->input('count', 30));
        return $requests;
    }

    public function setDonateRequestStatus() {
        $donate_request = DonateRequest::findOrFail(request()->input('request_id'));
        $donate_request->status = request()->input('status', 1);
        if (request()->filled('admin_comment')) {
            $donate_request->admin_comment = request()->input('admin_comment');
        }
        $donate_request->save();
        return [
            'message' => 'global.saved',
            'request' => $donate_request
        ];
    }

    public function getServers() {
        $video_servers = (new VideoServerAPI())->getServersList();
        foreach ($video_servers as $url => &$video_server) {
            $video_server['statistics_url'] = str_replace("http://", "https://", $video_server['statistics_url']);
        }
        $radio_servers = (new RadioAPI())->getServersList();
        return ['video_servers' => $video_servers, 'radio_servers' => $radio_servers];
    }

    public function updateLanguages() {
        $languages = request()->input('languages');
        file_put_contents(public_path("languages.json"), json_encode($languages, JSON_UNESCAPED_UNICODE));
        return ['message'=>'global.saved'];
    }


    public function updateSiteConfig() {
        $config = json_decode(file_get_contents(storage_path('config.json')), 1);
        $data = request()->validate([
            'smileys' => 'array',
            'smileys.*.code' => 'sometimes',
            'smileys.*.id' => ['required', new PictureId],
        ]);
        if (isset($data['smileys'])) {
            foreach ($data['smileys'] as &$smiley) {
                $picture = Picture::find($smiley['id']);
                $smiley['full_url'] = $picture->full_url;
            }
        }
        foreach ($config as $key => $val) {
            if (isset($data[$key])) {
                $config[$key] = $data[$key];
            }
        }
        file_put_contents(storage_path('config.json'), json_encode($config));

        return [
            'message' => 'global.saved'
        ];
    }

}
