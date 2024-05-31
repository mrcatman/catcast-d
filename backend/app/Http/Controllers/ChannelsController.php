<?php

namespace App\Http\Controllers;

use App\Helpers\FiltersHelper;
use App\Models\Broadcast;
use Illuminate\Support\Facades\DB;

use App\Helpers\CommonResponses;
use App\Helpers\ConfigHelper;
use App\Helpers\CryptoHelper;
use App\Helpers\PermissionsHelper;
use App\Helpers\StatisticsHelper;
use App\Helpers\LocalizationHelper;

use App\Models\Tag;
use App\Models\UserChannelPermissions;
use App\Models\Channel;


class ChannelsController extends Controller {


    public function index() {
        $channels = Channel::visible()->notBlocked();
        $user = auth()->user();

        if (request()->input('my')) {
            if (!$user) {
                return CommonResponses::unauthorized();
            }
            $channels = $channels->forUser($user);
        }
        $channels = FiltersHelper::applyFromRequest($channels, Channel::class);
        $channels->getCollection()->transform(function ($channel){
            $channel->load(['user:id,username']);
            return $channel;
        });

        if (request()->input('with_permissions')) {
            $permissions = UserChannelPermissions::where('user_id', $user->id)->get();

            $channels->getCollection()->transform(function ($channel) use ($permissions) {
                $permissions_for_channel = $permissions->firstWhere('channel_id', $channel->id);
                if ($permissions_for_channel) {
                    $channel->permissions = $permissions_for_channel->permissions;
                } else {
                    $channel->permissions = ['owner' => UserChannelPermissions::PERMISSIONS_STANDARD];
                }
                $channel->can_leave_team = !isset($permissions['owner']);
                return $channel;
            });
        }

        return $channels;
    }

    public function show($handle) {
        $channel = request()->input('find_by') == 'shortname' ? Channel::where(['shortname' => $handle])->firstOrFail() : Channel::findOrFail($handle);
        if ($channel->is_banned) {
            return response()->json([
                'message' => 'errors.channel_is_banned',
                'is_banned' => true,
                'ban_reason' => $channel->ban_reason
            ], 403);
        }
        if (!request()->has('do_not_count_stat')) {
            $channel->today_views++;
            $channel->save();
            StatisticsHelper::increment($channel);
        }
        $channel->append('additional_settings');
        return $channel;
    }



    public function store() {
        $user = auth()->user();
        $validation_rules = [
            'name' => 'required|max:255',
            'shortname' => 'required|alpha_dash|max:120|unique:channels',
            'description' => 'sometimes',
            'tags' => 'sometimes',
        ];
        $data = request()->validate($validation_rules, LocalizationHelper::getFormErrors('dashboard.create.errors'));
        $type = request()->input('channel_type', Channel::TYPE_TV);
        if (!ConfigHelper::channelTypeAllowed($type)) {
            return CommonResponses::validationError(['name' => ['dashboard.create.errors.channel_type_disabled']]);
        }

        $limit = ConfigHelper::maxChannelsCount($type);
        $type_id = Channel::TYPE_NAMES_MAP[$type];
        $already_created_count = Channel::where(['user_id' => $user->id, 'channel_type' => $type_id])->count();
        if ($already_created_count >= $limit) {
            return CommonResponses::validationError(['name' => [['text' => 'dashboard.create.errors.channels_limit_exceeded', 'params' => ['limit' => $limit]]]]);
        }


        $channel = new Channel($data);
        $channel->user_id = $user->id;
        $channel->channel_type = $type_id;
        CryptoHelper::generateKeys($channel);
        $channel->save();

        if (request()->filled('tags')) {
            $channel->tags = request()->input('tags');
            $channel->save();
        }
        if ($channel->is_radio) {
            //todo: init radio
//            $port = Channel::max('radio_port');
//            if (!$port) {
//                $port = 10000;
//            } else {
//                $port = $port + 1;
//            }
//            $channel->radio_port = $port;
//            $channel->save();
            //$radio_connection = new RadioConnection([
            //   'channel_id'=>$channel->id,
            //  'radio_id'=>-1,
            //   'radio_data'=>'',
            //   'radio_server'=>$radio_api->currentServer
            // ]);
            // $channel->radioConnection()->save($radio_connection);
//            $radio_api = new RadioAPI();
//            $server = $radio_api->getServerByChannelId($channel->id);
//            $radio_api->request($server, 'create_script', ['id' => $channel->id, 'port' => $port]);
        }
        return $channel;
    }

    public function update($id) {
        $channel = Channel::findOrFail($id);
        PermissionsHelper::checkHasAny($channel);

        $validation_rules = [ // todo: unify rules for create and update
            'name' => 'sometimes|required|max:255',
            'description' => 'sometimes',
            'shortname' => 'sometimes|required|max:120',
            'show_offline'=>'sometimes|boolean',
            'show_timetable'=>'sometimes|boolean',
            'chat_motd' => 'sometimes|max:255',
            'allow_guests' => 'sometimes|boolean',
            'pictures_data' => 'sometimes',
            'colors_scheme' => 'sometimes|array',
            'tags' => 'sometimes|array',
            'links' => 'sometimes|array|nullable',
            'links.*.title'=>'required',
            'links.*.url'=>'required',
        ];

        $data = request()->validate($validation_rules);
        if (isset($data['shortname']) && $data['shortname'] != $channel->shortname) {
            $shortname_occupied = Channel::where(['shortname' => $data['shortname']])->count() > 0;
            if ($shortname_occupied) {
                return CommonResponses::validationError([
                    'shortname' => ['dashboard.info.errors.shortname_unique']
                ]);
            }
        }


        if (PermissionsHelper::getStatus(['edit_info'], $channel)) {
            foreach($data as $key => $value) {
                $channel->{$key} = $value;
            }
            $channel->save();
        }
        if (request()->filled('additional_settings')) {
            $channel->additional_settings = request()->input('additional_settings');
            $channel->save();
        }
        return $channel;
    }




    public function destroy($id) {
        $channel = Channel::find($id);
        PermissionsHelper::check([], $channel);
        $channel->delete();
        return [
            'message' => 'dashboard.info.messages.channel_deleted'
        ];
    }


    public function restore($id) {
        $channel = Channel::withTrashed()->find($id);
        PermissionsHelper::check([], $channel);
        $channel->restore();
        $channel->deleted_at = null;
        $channel->save();
        return ['message' => 'dashboard.info.messages.channel_restored'];
    }



    public function getTags() {
        $limit = request()->input('limit', 16);
        $radio_ids = Channel::where(['channel_type' => 1])->pluck('id');

        $tags = Tag::groupBy('tag')
            ->select(['tags.*', DB::raw('COUNT(*) as count')])
            ->orderByRaw('COUNT(*) DESC')
            ->where(['entity_type' => 'channels']);

        if (request()->filled('type')) {
            $is_radio = request()->input('type') == "radio";
            if ($is_radio) {
                $tags = $tags->whereIn('entity_id', $radio_ids);
            } else {
                $tags = $tags->whereNotIn('entity_id', $radio_ids);
            }
        }
        if (!request()->filled('random')) {
            $tags = $tags->limit($limit);
        }
        $tags = $tags->get();
        if (request()->filled('random')) {
            $tags = $tags->shuffle();
            $tags = $tags->slice(0, $limit);
        }
        $data = [];
        foreach ($tags as $tag) {
            $data[] = [
                'tag' => $tag->tag,
                'count' => $tag->count
            ];
        }
        return $data;
    }


    public function getDeleted() {
        return Channel::withTrashed()->whereNotNull('deleted_at')->where(['user_id' => auth()->user()->id])->get();
    }

    public function getLeft() {
        $ids = UserChannelPermissions::withTrashed()->whereNotNull('deleted_at')->where(['user_id' => auth()->user()->id])->pluck('channel_id');
        return Channel::withTrashed()->whereIn('id', $ids)->get();
    }

}
