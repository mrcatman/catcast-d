<?php

namespace App\Helpers;

use App\Models\Broadcast;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class NginxRtmpHelper {

    public static function getLogFilePath() {
        return public_path('nginx_test_log.txt');
    }

    public static function updateViewersCounts() {
        $log = FilesHelper::tail(self::getLogFilePath(), 4096);
        $log = explode(PHP_EOL, $log);
        $ips = [];

        $start_date = Carbon::now();
        $start_date->subMinutes(1);
        $start_date->subDays(1);

        foreach ($log as $line) {
            preg_match('/(.*?) - - \[(.*?)\] "GET \/hls\/(.*?).m3u8 HTTP/', $line, $output_array);
            if (count($output_array) === 4) {
                $date = Carbon::parse($output_array[2]);
                if ($date > $start_date) {
                    $ip = $output_array[1];
                    $channel_id = $output_array[3];
                    if (!isset($ips[$channel_id])) {
                        $ips[$channel_id] = [];
                    }
                    if (!in_array($ip, $ips[$channel_id])) {
                        $ips[$channel_id][] = $ip;
                    }
                }
            }
        }
        foreach ($ips as $channel_id => $ips_list) {
            $broadcast = Broadcast::where(['channel_id' => $channel_id])->whereNull('ended_at')->first();
            if ($broadcast) {
                $broadcast->viewers = count($ips_list);
                $broadcast->save();
            }
        }
    }

    /**
     * Start or stop stream recording by making a request to nginx-rtmp's control module
     * @param int $channel_id Channel ID
     * @param boolean $record_state true - start, false - stop
     * @void
     */
    public static function changeRecordState($channel_id, $record_state) {
        $command = $record_state ? 'start' : 'stop';
        $app = ConfigHelper::rtmpAppName();
        $url = "http://nginx/internal/control/record/$command?app=$app&name=$channel_id&rec=main";
        Http::get($url);
    }

}
