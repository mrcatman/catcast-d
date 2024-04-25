<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;

use App\Models\User;
use App\Models\Like;
use App\Models\Media;

class UsersController extends Controller {

    public function friendsRequests() {
        $user = auth()->user();
        $list = $user->friendsRequests()->get();
        $count = count($list);
        return [
            'list' => $list,
            'count' => $count,
        ];
    }

    public function infoFields() {
        return User::infoFields();
    }

    public function autocomplete() {
        $autocomplete = request()->input('search');
        if (!$autocomplete || mb_strlen($autocomplete, 'UTF-8') < 3) {
            return [];
        }
        $users = User::where('username', 'LIKE', '%' . $autocomplete . '%')->select(['id', 'username'])->get();
        return $users;
    }

    public function index() {
        $count = request()->filled('count') ? request()->input('count') : 10;
        $data = User::orderBy('last_seen','desc');
        if (request()->filled('search')) {
            $data = $data->search(request()->input('search'));
        }
        $data = $data->paginate($count);
        if (auth()->user()) {
            $data->getCollection()->transform(function ($user) {
                $user->friend_state = $user->friend_state;
                return $user;
            });
        }
        return $data;
    }

    public function show($id) {
        $user = User::findOrFail($id);
        if (!$user->checkPrivacySettings('can_view_profile')) {
            $user_data = [
                'id' => $user->id,
                'username' => $user->username,
                'avatar' => $user->avatar,
                'last_seen' => $user->last_seen,
            ];
            return $user_data;
        }
        $user->load('info');
        $user->append('links');
        $user->is_admin = $user->is_admin;
        return $user;
    }

    public function getAccessSettings($id) {
        $user = User::findOrFail($id);
        return [
            'ban' => $user->ban,
            'can_view_profile' => $user->checkPrivacySettings('can_view_profile'),
            'comments_enabled' => $user->checkPrivacySettings('can_write_on_wall'),
            'comments_display' => $user->checkPrivacySettings('can_view_profile'),
            'friend_state' => $user->friend_state,
        ];
    }

    private function getFriendsForUser($user) {
        if (!$user->checkPrivacySettings('can_view_profile')) {
            return CommonResponses::unauthorized();
        }
        $show = request()->input('show', 'all');
        if ($show === 'common') {
            $friends = $user->friends()->common($user->id);
        } elseif ($show === 'requests') {
            $friends = $user->friendsRequests();
        } else {
            $friends = $user->friends();
        }
        //$friends = User::query();
        $friends = $friends->orderBy('last_seen', 'desc')->paginate(request()->input('count', 12));
        if (auth()->user()) {
            $friends->getCollection()->transform(function ($user) {
                $user->append('friend_state');
                return $user;
            });
        }
        return $friends;
    }

    public function getFriends($id) {
        $user = User::findOrFail($id);
        return $this->getFriendsForUser($user);
    }

    public function myFriends() {
        $user = auth()->user();
        return $this->getFriendsForUser($user);
    }


    public function friendsRequest($id) {
        $friend_user = User::findOrFail($id);
        $user = auth()->user();
        if (request()->filled('hide')) {
            $friends_request = Like::where(['entity_type' => 'friends', 'user_id' => $friend_user->id, 'entity_id' => $user->id])->first();
            $friends_request->save();
            $list = $user->friendsRequests()->get();
            $count = count($list);
            return [
                'list' => $list,
                'count' => $count,
            ];
        } elseif (request()->filled('state')) {
            $state = (bool)request()->input('state');
            if ($state) {
                $like = Like::where(['entity_type' => 'friends', 'user_id' => $user->id, 'entity_id' => $friend_user->id])->firstOrNew([
                    'user_id' => $user->id,
                    'entity_type' => 'friends',
                    'entity_id' => $friend_user->id
                ]);
                $like->save();
                if ($friend_user->friend_state === "STATE_IN_FRIENDS") {
                    (new FriendsRequestAccepted($user, $friend_user))->sendToUser($friend_user);
                } else {
                    (new FriendsRequest($user, $friend_user))->sendToUser($friend_user);
                }
            } else {
                Like::where([
                    'user_id' => $user->id,
                    'entity_type' => 'friends',
                    'entity_id' => $friend_user->id
                ])->delete();
            }
            $list = $user->friendsRequests()->get();
            $count = count($list);
            return [
                'state' => $friend_user->friend_state,
                'list' => $list,
                'count' => $count,
            ];
        } else {
            return CommonResponses::unauthorized();
        }
    }




    public function videos($id) {
        $user = User::findOrFail($id);
        $videos = Media::where(['user_id' => $user->id])->visible();
        $order = request()->input('order', 'new');
        if ($order == 'old') {
            $videos = $videos->orderBy('id', 'asc');
        } elseif ($order == 'popularity') {
            $videos = $videos->orderBy('views', 'desc');
        } else {
            $videos = $videos->orderBy('id', 'desc');
        }
        if (request()->filled('search')) {
            $search = request()->input('search');
            $videos = $videos->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', '%' . $search . '%');
                $query->orWhere('description', 'LIKE', '%' . $search . '%');
            });
        }
        $videos = $videos->paginate(request()->input('count', 16));
        return $videos;
    }


}
