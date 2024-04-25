<?php

namespace App\Http\Controllers;

use App\Models\Channel;
use App\Models\Playlist;
use App\Notifications\ChannelConnections\NotificationChannelConnection;
use App\Notifications\Types\BaseNotificationType;
use Illuminate\Support\Carbon;

use App\Helpers\CommonResponses;

use App\Models\Notification;
use App\Models\NotificationBinding;
use App\Models\NotificationSubscription;

use App\Notifications\Types\ChannelNewMedia;
use App\Notifications\Types\NewPermissionRequest;
use App\Notifications\Types\PlaylistNewMedia;
use App\Notifications\Types\NewBroadcast;
use App\Notifications\Types\ChannelNewPost;

use App\Notifications\ChannelConnections\VKChannelConnection;
use App\Notifications\ChannelConnections\EmailChannelConnection;
use App\Notifications\ChannelConnections\TelegramChannelConnection;
use App\Notifications\ChannelConnections\BroadcastChannelConnection;
use Illuminate\Validation\Rule;


class NotificationsController extends Controller
{

    private $channels = [
        EmailChannelConnection::class,
        TelegramChannelConnection::class,
        VKChannelConnection::class,
        BroadcastChannelConnection::class,
    ];

    private $categories;

    public function __construct() {
        $this->categories = collect([
            [
                'display_name' => 'notifications.categories.profile',
                'events' => [
                    'notifications.types.permission_request.heading' => NewPermissionRequest::class,
                ],
            ],
            [
                'entity' => Channel::class,
                'display_name' => 'notifications.categories.channels',
                'events' => [
                    'notifications.types.new_broadcast.heading' => NewBroadcast::class,
                    'notifications.types.new_post.heading' => ChannelNewPost::class,
                    'notifications.types.new_media.heading' => ChannelNewMedia::class,
                ],
            ],
            [
                'entity' => Playlist::class,
                'display_name' => 'notifications.categories.playlists',
                'events' => [
                    'notifications.types.new_media.heading' => PlaylistNewMedia::class
                ],
            ],
//        'programs'=>[
//            'name'=>'notifications.types.programs.heading',
//            'subtypes'=> [
//                ProgramIsOnline::class
//            ],
//        ],
        ]);
    }

    /**
     * Get specific notification class by provided event type ID
     * @param string $needed_id
     * @return BaseNotificationType
     */
    private function getNotificationClassByEventTypeId($needed_id) {
        return $this->categories->pluck('events')->flatten()->keyBy(function($class) {
            return $class::getEventTypeId();
        })->get($needed_id);
    }

    /**
     * Get specific channel connection class by provided ID
     * @param string $needed_id
     * @return NotificationChannelConnection
     */
    private function getChannelClassById($needed_id) {
        return $this->getChannels()->keyBy('id')->get($needed_id);
    }

    public function getChannels() {
        $user = auth()->user();
        $channels = collect([]);
        foreach ($this->channels as $channel) {
            if ($channel::enabled() && $channel::canSubscribe($user)) {
                $channels->push([
                    'name' => $channel::getDisplayName(),
                    'id' => $channel::getId(),
                ]);
            }
        }
        return $channels;
    }

    public function getEvents() {
        return $this->categories->map(function($category) {
            return [
                'category_name' => $category['display_name'],
                'entity_type' => isset($category['entity']) ? $category['entity']::getEntityType() : null,
                'events' => collect($category['events'])->map(function($type, $type_name) {
                    return [
                        'id' => $type::getEventTypeId(),
                        'name' => $type_name
                    ];
                })->values()
            ];
        })->values();
    }

    public function getBindings() {
        return NotificationBinding::where(['user_id' => auth()->user()->id])->get()->groupBy('type')->map(function($bindings) {
            return $bindings->pluck('channel');
        });
    }

    public function saveBindings() {
        $user = auth()->user();
        NotificationBinding::where(['user_id' => $user->id])->delete();
        foreach (request()->all() as $type_id => $notification_channel_ids) {
            if (!$this->getNotificationClassByEventTypeId($type_id)) {
                continue;
            }
            foreach ($notification_channel_ids as $notification_channel_id) {
                if (!$this->getChannelClassById($notification_channel_id)) {
                    continue;
                }
                $binding = new NotificationBinding([
                    'user_id' => $user->id,
                    'type' => $type_id,
                    'channel' => $notification_channel_id
                ]);
                $binding->save();
            }
        }
        return $this->getBindings();
    }

    public function getSubscriptions()
    {
        return NotificationSubscription::where(['user_id' => auth()->user()->id])->get();
    }

    public function getSubscriptionsForEntity($entity_type, $entity_id)
    {
        return NotificationSubscription::where(['user_id' => auth()->user()->id])
            ->where(['entity_type' => $entity_type, 'entity_id' => $entity_id])
            ->get();
    }

    public function setSubscriptionsForEntity($entity_type, $entity_id) {
        $user = auth()->user();
        $category = $this->categories->filter(function($category) use ($entity_type) {
            return isset($category['entity']) && $category['entity']::getEntityType() == $entity_type;
        })->first();

        if (!$category) {
            return CommonResponses::notFound();
        }

        $entity = $category['entity']::findOrFail($entity_id);
        $available_event_type_ids = array_map(function($event) {
            return $event::getEventTypeId();
        }, $category['events']);
        $data = request()->validate([
            'event_types' => [
                'array',
            ],
            'event_types.*' => [
                Rule::in($available_event_type_ids)
            ]
        ]);

        NotificationSubscription::where(['user_id' => $user->id])
            ->where(['entity_type' => $entity_type, 'entity_id' => $entity_id])
            ->delete();

        $event_types = $data['event_types'];

        $subscriptions = [];
        foreach ($event_types as $event_type) {
            $subscription = new NotificationSubscription([
                'user_id' => $user->id,
                'entity_id' => $entity->id,
                'entity_type' => $entity_type,
                'event_type' => $event_type
            ]);
            $subscription->save();
            $subscriptions[] = $subscription;
        }
        return $subscriptions;
    }

    public function index(){
        $user = auth()->user();
        $notifications = $user->notifications();
        if (request()->has('search')) {
            $notifications = $notifications->where('data', 'LIKE', '%'.request()->input('search').'%');
        }
        $notifications = $notifications->paginate(request()->input('count', 10));
        $notifications->getCollection()->transform(function ($notification) use ($user) {

            $type_class = $this->getNotificationClassByEventTypeId($notification->type);
            $entity = ($type_class::getEntityClass())::find($notification->entity_id);

            $notification_instance = new $type_class($entity);
            if ($entity) {
                $data = $notification_instance->toBroadcast($user)->data;
                $data['type'] = $notification->type;
                $data['id'] = $notification->id;
                $data['is_read'] = $notification->is_read;
                $data['created_at'] = $notification->created_at;
                return $data;
            } else {
               // $notification->delete();
            }
            return null;
        })->filter(function ($notification) {
            return !!$notification;
        });
        $user->unreadNotifications->markAsRead();
        return $notifications;
    }

    public function delete($id) {
        $notification = Notification::findOrFail($id);
        if ($notification && $notification->notifiable_id == auth()->user()->id) {
            $notification->delete();
            return ['message' => 'notifications.messages.success_deleted'];
        } else {
            return CommonResponses::unauthorized();
        }
    }

    public function restore($id) {
        $notification = Notification::withTrashed()->findOrFail($id);
        if ($notification && $notification->notifiable_id == auth()->user()->id) {
            $notification->restore();
            return ['message' => 'notifications.messages.success_restored'];
        } else {
            return CommonResponses::unauthorized();
        }
    }


}
