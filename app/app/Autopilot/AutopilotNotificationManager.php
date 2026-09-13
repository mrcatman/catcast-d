<?php
namespace App\Autopilot;
use App\Models\Channel;
use App\Notifications\ProgramIsOnline;
use App\ProgramNotification;
use App\Models\Media;
use Illuminate\Support\Carbon;

class AutopilotNotificationManager {
   public static function send() {
       $time = request()->input('time',time());
       $time = Carbon::createFromTimestamp($time);
       $subscriptions = ProgramNotification::where('time', '>=', $time->subMinutes(5))->where('time', '<=', $time->addMinutes(5))->get();
       foreach ($subscriptions as $subscription) {
           (new ProgramIsOnline($subscription))->sendToUser($subscription->user);
       }
       return $subscriptions;
   }
}
