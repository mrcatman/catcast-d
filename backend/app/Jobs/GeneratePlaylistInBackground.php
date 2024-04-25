<?php

namespace App\Jobs;

use App\Autopilot\AutopilotPlaylistGenerator;
use App\Helpers\VideoServerAPI;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeneratePlaylistInBackground implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel_id;

    public function __construct($channel_id)
    {
        $this->channel_id = $channel_id;
    }

    public function handle() {
	    ini_set('memory_limit', '5000M');
	    $time_start = time();
        $time_end = time() + 86400;
        if ($this->channel_id == 33418) {
            $time_end = time() + 86400 * 7;
        }
        $generated_playlist = AutopilotPlaylistGenerator::generateForChannel($this->channel_id, $time_start, $time_end, [
            'remove_old' => true
        ]);
        if ($special_server = VideoServerAPI::getSpecialServer($this->channel_id)) {
            $response = VideoServerAPI::request('update_playlists', [
                [
                    'data' => json_encode($generated_playlist),
                    'channel_id' => $this->channel_id
                ]
            ], $special_server);
        } else {
            $response = VideoServerAPI::requestAll('update_playlists', [
                [
                    'data' => json_encode($generated_playlist),
                    'channel_id' => $this->channel_id
                ]
            ], VideoServerAPI::getSpecialServersList());
        }
        return true;
    }
}
