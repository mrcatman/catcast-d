<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;

use App\Models\User;
use App\Models\Media;

class UsersController extends Controller {

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

    public function index() { // todo: filter and sort (?)
        $count = request()->filled('count') ? request()->input('count') : 10;
        $data = User::orderBy('last_seen','desc');
        if (request()->filled('search')) {
            $data = $data->search(request()->input('search'));
        }
        $data = $data->paginate($count);
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
