<?php
namespace App\Autopilot;
use App\Models\Channel;
use App\Helpers\VideoServerAPI;
use App\Notifications\ProgramIsOnline;
use App\PremiumPackages\OpenStreamTVPremiumPackage;
use App\PremiumService;
use App\ProgramNotification;
use App\Models\Media;
use Illuminate\Support\Carbon;

class AutopilotStreamManager {

   public static function updateOpenStreams() {
       $package_id = (new OpenStreamTVPremiumPackage())->getPackageId();
       $services = PremiumService::where(['package_id' => $package_id])->get();
       $data = [];
       foreach ($services as $service) {
           $data[$service->channel_id] = $service->paid_till->timestamp;
       }
       $response = VideoServerAPI::requestAll("update_open_streams", [
           'data' => $data
       ]);
        return $response;
   }

    public static function updatePasswords() {
        $channels = Channel::where('stream_password', '!=', '')->pluck('stream_password', 'id');
        $response = VideoServerAPI::requestAll("update_channels_passwords", [
            'data' => $channels
        ]);
        return $response;
    }
}
