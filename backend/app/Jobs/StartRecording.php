<?php

namespace App\Jobs;

use App\Models\Announce;
use App\Models\Channel;
use App\Models\Broadcast;
use App\Models\Recording;
use App\Models\TVServer;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class StartRecording implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel;
    protected $channel_online;
    protected $announce;
    protected $need_send_request;

    public function __construct(Channel $channel, Broadcast $channel_online, Announce $announce = null, $need_send_request = true)
    {
        $this->channel = $channel;
        $this->channel_online = $channel_online;
        if ($announce) {
            $this->announce = $announce;
        }
        $this->need_send_request = $need_send_request;
    }

    public function handle()
    {
        $channel = $this->channel;
        $channel_online = $this->channel_online;
        $recording = new Recording();
        $recording->channel_id = $channel->id;
        $recording->start_time = time();
        $recording->status = "STATUS_STARTED";
        $recording->stream_name = $channel_online->stream_name;
        $recording->channel_online_id = $channel_online->id;
        $recording->server_id = $channel_online->server_id;
        $recording->user_id = $channel_online->user_id;
        $recording->title = $channel->program_name;

        if ($this->announce) {
            $recording->title = $this->announce->title;
            $recording->announce_id = $this->announce->id;
        }

        if ($this->need_send_request) {
            $res = BroadcastServers::startRecord(TVServer::find($recording->server_id), $channel);
            $file_name = explode("/", $res);
            $file_name = $file_name[count($file_name) - 1];
            $recording->file_name = $file_name;
        } else {
            $recording->file_name = "";
        }
       // $recording->save();
        return true;
    }
}
