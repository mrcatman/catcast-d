<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Models\User;
use App\Models\UserInteractPermission;
use App\Models\UserChannelPermissions;
use App\Models\Channel;

use Illuminate\Http\Request;

class ChannelTeamController extends Controller {

    public function getAvailablePermissionsList() {
        return UserChannelPermissions::LIST;
    }

    protected function transformData($team_member) {
        return [
            'id' => $team_member->user->id,
            'username' => $team_member->user->username,
            'avatar' => $team_member->user->avatar,
            'confirmed' => $team_member->confirmed,
            'position' => $team_member->position,
            'permissions' => $team_member->permissions,
            'full_permissions' => $team_member->permissions_full
        ];
    }

    public function getTeamForChannel($channel_id) {
        $channel = Channel::findOrFail($channel_id);

        $all_types = PermissionsHelper::getAvailableTypeIds();
        $all_types[] = 'owner';
        $all_types[] = 'admin';

        $types = request()->input('types', $all_types);
        $owner = $channel->user;
        $team = [];
        if ($owner) {
            if ($types->contains('owner')) {
                $team[] = [
                    'permissions' => [
                        'owner'
                    ],
                    'full_permissions' => [],
                    'id' => $owner->id,
                    'username' => $owner->username,
                    'avatar' => $owner->avatar,
                    'position' => '',
                    'confirmed' => true,
                ];
            }
        }
        $team_list = UserChannelPermissions::where(['channel_id' => $channel_id]);
        if (!request()->input('all') || !PermissionsHelper::getStatus([], $channel)) {
            $team_list = $team_list->where(['confirmed' => true]);
        }
        $team_list = $team_list->get();
        foreach ($team_list as $team_member) {
            if ($team_member->user) {
                $is_author = $team_member->user->id == $channel->user_id;


                $should_add = false;
                foreach ($team_member->permissions as $right) {
                     if ($types->contains($right)) {
                        $should_add = true;
                    }
                }
                if ($team_member->permissions_full) {
                    foreach ($team_member->permissions_full as $right) {
                        if ($types->contains($right)) {
                            $should_add = true;
                        }
                    }
                }
                if ($is_author) {
                    $team[0]['position'] = $team_member->position;
                } elseif ($should_add) {

                    $team[] = $this->transformData($team_member);
                }
            }
        }
        $team = array_values($team);
        return $team;
    }

    public function getChannelsForUser($id) {
        $user = User::findOrFail($id);
        if (!$user->checkPrivacySettings('can_view_profile')) {
            return CommonResponses::unauthorized();
        }
        $created_channels = Channel::visible()->where(['user_id'=>$user->id])->get();
        $permissions = UserChannelPermissions::where(['user_id'=>$user->id, 'confirmed' => true])->get();
        $channels = [];
        foreach ($created_channels as $channel) {
            $channels[] = [
                'owner' => true,
                'channel'=>[
                    'id'=>$channel->id,
                    'shortname'=>$channel->shortname,
                    'name'=>$channel->name,
                    'logo'=>$channel->logo
                ]
            ];
        }
        foreach ($permissions as $permissions_item) {
            $permissions_data = [
                'position'=>$permissions_item->position,
                'permissions'=>[],
            ];
            $channel = $permissions_item->channel;
            if ($channel) {
                if ($user->id == $channel->user_id) {
                    foreach ($channels as &$channel_data) {
                        if ($channel_data['channel']['id'] === $channel->id) {
                            $channel_data['position'] = $permissions_item->position;
                        }
                    }
                } else {
                    $permissions_data['channel'] = [
                        'id' => $channel->id,
                        'shortname' => $channel->shortname,
                        'name' => $channel->name,
                        'logo' => $channel->logo
                    ];
                    $channels[] = $permissions_data;
                }
            }
        }
        $channels = collect($channels);
        return $channels;
    }

    private function createOwnerPermissionsIfNotExists($channel) {
        $user_permissions = UserChannelPermissions::firstOrNew(['user_id' => $channel->user_id, 'channel_id' => $channel->id]);
        if (!$user_permissions->exists) {
            $user_permissions->permissions = ['owner' => UserChannelPermissions::PERMISSIONS_STANDARD];
            $user_permissions->confirmed = true;
            $user_permissions->created_at = $channel->created_at;
            $user_permissions->save();
        }
    }


    public function getForManager($id) {
        $channel = PermissionsHelper::getChannelIfAllowed($id, ['team']);
        $user = auth()->user();

        $full_permissions = PermissionsHelper::getStatus(['team'], $channel, null, true);

        $this->createOwnerPermissionsIfNotExists($channel);

        $team = $channel->team()->withTrashed()->with(['user:id,username','added_by:id,username'])->orderBy('created_at', 'asc');
        if (request()->has('search')) {
            $team = $team->whereHas('user', function($query) {
                return $query->where('username', 'like', '%'.request()->input('search').'%');
            });
        }
        $team = $team->paginate(request()->input('count', 20));
        $team->getCollection()->transform(function($item) use ($user, $full_permissions) {
            $is_current_user = $user->id == $item->user_id;
            $item->can_edit = !$is_current_user && ($full_permissions || $item->added_by_user_id == $user->id);
            $item->can_delete = !$is_current_user && $item->can_edit && !isset($item->permissions['owner']);
            return $item;
        });
        return $team;
    }

    public function update($id) {
        $channel = PermissionsHelper::getChannelIfAllowed($id, ['team']);
        $user = auth()->user();

        $rules = [
            'user_id' => 'required_without:username',
            'username' => 'required_without:user_id',
            'position' => 'sometimes|max:255',
            'hidden' => 'sometimes|boolean',
            'permissions' => 'required'
        ];
        foreach (UserChannelPermissions::LIST as $permission_type) {
            if (!isset($permission_type['can_be_added']) || $permission_type['can_be_added']) {
                $rules['permissions.' . $permission_type['id']] = (isset($permission_type['can_be_full']) && $permission_type['can_be_full']) ? 'in:0,1,2' : 'in:0,1';
            }
        }
        $data = request()->validate($rules);
        $data['permissions'] = array_filter($data['permissions'], function($val) {
            return $val > 0;
        });

        $user_to_add = isset($data['user_id']) ? User::findOrFail($data['user_id']) : User::where(['username' => $data['username']])->firstOrFail();
        $user_permissions = UserChannelPermissions::firstOrNew(['user_id' => $user_to_add->id, 'channel_id' => $channel->id]);
        if ($user_to_add->id == $user->id || ($user_permissions->exists && $channel->user_id != $user->id && $user_permissions->added_by_user_id != $user->id)) {
            return CommonResponses::unauthorized();
        }

        unset($data['username']);
        $user_permissions->fill($data);
        $user_permissions->user_id = $user_to_add->id;
        $user_permissions->channel_id = $channel->id;
        if (!$user_permissions->added_by_user_id) {
            $user_permissions->added_by_user_id = $user->id;
        }

        if (!$user_permissions->exists) {
            $interact_permission_status = UserInteractPermission::checkStatus($user_to_add->id, $channel->id, Channel::getEntityType());
            if ($interact_permission_status === UserInteractPermission::STATUS_ALLOWED) {
                $user_permissions->confirmed = true;
            } elseif ($interact_permission_status === UserInteractPermission::STATUS_BLOCKED) {
                return response()->json(['message' => 'dashboard.team.errors.user_has_blocked_channel'], 403);
            } else {
                $permissions_for_user = UserInteractPermission::checkStatus($user_to_add->id, $user->id, User::getEntityType(), true, true);
                if ($permissions_for_user === UserInteractPermission::STATUS_ALLOWED) {
                    $user_permissions->confirmed = true;
                } elseif ($permissions_for_user === UserInteractPermission::STATUS_BLOCKED) {
                    return response()->json(['message' => 'dashboard.team.errors.user_has_blocked_you'], 403);
                } else {
                    $user_permissions->confirmed = false;
                }
            }
        }
        $user_permissions->save();
        return $user_permissions;
    }

    public function delete($id, $user_id) {
        $channel = Channel::findOrFail($id);
        $user_permissions = UserChannelPermissions::where(['user_id' => $user_id, 'channel_id' => $channel->id])->firstOrFail();
        PermissionsHelper::check(['team'], $channel, $user_permissions);

        $user = auth()->user();
        if ($user_permissions->user->id == $user->id) {
            return CommonResponses::unauthorized();
        }
        $user_permissions->delete();
        return ['message' => 'dashboard.team.messages.user_deleted_from_team'];
    }

    public function leave($id) {
        $permissions = UserChannelPermissions::where(['user_id' => auth()->user()->id, 'channel_id' => $id])->first();
        if ($permissions) {
            $permissions->delete();
            return [
                'message' => 'dashboard.leave.messages.left'
            ];
        } else {
            return CommonResponses::unauthorized();
        }
    }


    public function return($id){
        $permissions = UserChannelPermissions::withTrashed()->where(['user_id' => auth()->user()->id, 'channel_id' => $id])->first();
        if ($permissions) {
            $permissions->restore();
            return [
                'message' => 'dashboard.leave.messages.returned'
            ];
        } else {
            return CommonResponses::unauthorized();
        }
    }



}
