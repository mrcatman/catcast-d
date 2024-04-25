<?php

namespace App\Helpers;

use App\Models\Broadcast;
use App\Models\Channel;
use Carbon\Carbon;

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

}
