<?php

namespace App\Http\Controllers;

use App\Helpers\CommonResponses;
use App\Helpers\LocalizationHelper;
use App\Models\Notification;
use App\Models\NotificationBinding;

use App\Notifications\NewMessage;
use App\Notifications\NewPermissionRequest;
use App\Models\UserInteractPermission;
use App\Models\UserChannelPermissions;
use Illuminate\Http\Request;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Support\Facades\Validator;

use App\Notifications\ChannelConnections\VKChannelConnection;
use App\Notifications\ChannelConnections\EmailChannelConnection;
use App\Notifications\ChannelConnections\TelegramChannelConnection;
use App\Notifications\ChannelConnections\BroadcastChannelConnection;

use App\Notifications\ChannelGotOnline;
use App\Notifications\ChannelNewFeedPost;

class PermissionsController extends Controller {

    protected $available_types = [
        'channels',
        'users'
    ];

    public function set() {
        if ($user = auth()->user()) {
            if (request()->filled('entity_id') && request()->filled('status') && request()->filled('entity_type')) {
                if (in_array(request()->input('entity_type'), $this->available_types)){
                    $id = (int)request()->input('entity_id');
                    $status = (bool)request()->input('status') ? 1 : -1;
                    $type = request()->input('entity_type');
                    $permission = UserInteractPermission::firstOrNew(['user_id' => $user->id, 'entity_id' => $id, 'entity_type' => $type]);
                    $permission->status = $status;
                    $permission->save();

                    Notification::where([
                        'type' => 'App\Notifications\NewPermissionRequest',
                        'entity_type' => $type,
                        'entity_id' => $id,
                        'notifiable_id' => $user->id
                    ])->delete();

                    switch ($type) {
                        case 'channels':
                            if ($status == 1) {
                                UserChannelPermissions::where(['user_id' => $user->id, 'channel_id' => $id])->update(['is_confirmed' => true]);
                            } else {
                                UserChannelPermissions::where(['user_id' => $user->id, 'channel_id' => $id])->delete();
                            }
                            break;
                        default:
                            break;
                    }
                    return [
                        'status' => 1,
                    ];
                } else {
                    return response()->json(['message' => 'errors.no_parameters'], 422);
                }
            } else {
                return response()->json(['message' => 'errors.no_parameters'], 422);
            }
        } else {
            return CommonResponses::unauthorized();
        }
    }

}
