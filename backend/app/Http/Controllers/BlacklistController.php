<?php

namespace App\Http\Controllers;

use App\Models\BlacklistItem;
use App\Notifications\FriendsRequest;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class BlacklistController extends Controller
{
   public function index(){
        $blacklist = BlacklistItem::where(['from_id'=>auth()->user()->id])->with('user:id,username')->orderBy('id','desc')->get();
        return $blacklist;
    }

    public function store() {
       if (!request()->filled('id') && !request()->filled('username')) {
           return response()->json(['message' => 'blacklist.errors.no_id_or_username'], 422);
       } elseif (request()->filled('id')) {
           $user_to_ban = User::find(request()->input('id'));
       } elseif (request()->filled('username')) {
           $user_to_ban = User::where(['username' => request()->input('username')])->first();
       }
       if (!$user_to_ban) {
           return response()->json([
               'message' => 'blacklist.errors.no_such_user',
               'errors' => [
                   'username' => [
                       'blacklist.errors.no_such_user'
                   ]
               ]
           ], 422);
       }
        $user = auth()->user();

        if ($user_to_ban->id == $user->id) {
            return response()->json([
                'message' => 'blacklist.errors.self_ban',
                'errors' => [
                    'username' => [
                        'blacklist.errors.self_ban'
                    ]
                ]
            ], 422);
        }
        $already_in_blacklist = BlacklistItem::where(['from_id'=>$user->id])->where(['to_id'=> $user_to_ban->id])->first();
        if ($already_in_blacklist) {
            return response()->json([
                'message' => 'blacklist.errors.already_in_blacklist',
                'errors' => [
                    'username' => [
                        'blacklist.errors.already_in_blacklist'
                    ]
                ]
            ], 422);
        }
        $blacklist_item = new BlacklistItem();
        $blacklist_item->from_id = $user->id;
        $blacklist_item->to_id = $user_to_ban->id;
        $blacklist_item->save();
        $blacklist_item->load('user:id,username');
        return [
            'message' => 'blacklist.success_added',
            'item' => $blacklist_item,
        ];
    }


    public function destroy($id) {
        BlacklistItem::where(['from_id'=>auth()->user()->id])->where(['to_id'=>$id])->delete();
        return ['message '=> 'blacklist.success_deleted'];
    }
}
