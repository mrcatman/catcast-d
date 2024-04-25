<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class syncPlaylists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'playlists:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \App\Autopilot\AutopilotPlaylist::where('channel_id', '>', '0')->delete();
        $files = \Illuminate\Support\Facades\Storage::disk('channels_data')->files();
        foreach ($files as $file) {
            if (strpos($file, "playlists") !== false) {
                $contents = \Illuminate\Support\Facades\Storage::disk('channels_data')->get($file);
                $contents = json_decode($contents);
                $id = explode(".json", explode("_", $file)[1])[0];
                if ($contents && count($contents) > 0) {
                    foreach ($contents as $playlist_content) {
                        if (isset($playlist_content->items) && $playlist_content->items && count ($playlist_content->items) > 0) {
                            $playlist = new \App\Autopilot\AutopilotPlaylist();
                            $playlist->channel_id = $id;
                            $playlist->data = (object)[
                                "time_start" => $playlist_content->start,
                                "title" => "Playlist",
                                "cycled" => [
                                    "status" =>  isset($playlist_content->cycledTill)  ? (bool)$playlist_content->cycled : false,
                                    "till" => isset($playlist_content->cycledTill)  ? $playlist_content->cycledTill : 0,
                                ],
                                "type" => "default",
                                "playback_type" => "default",
                                "color" => "#fff"
                            ];
                            $playlist->save();
                            $i = 0;
                            foreach ($playlist_content->items as $playlist_item) {
                                $item = new \App\Autopilot\AutopilotItem();
                                if (isset($playlist_item->id) && $playlist_item->id > 0 && isset($playlist_item->length) && $playlist_item->length > 0 && isset($playlist_item->title)) {
                                    //echo $playlist_item->title.PHP_EOL;
                                    $item->playlist_id = $playlist->id;
                                    $item->video_id = $playlist_item->id;
                                    $item->title = $playlist_item->title;
                                    $item->index = $i;
                                    $item->length = $playlist_item->length;
                                    $item->can_subscribe = true;
                                    $item->can_view = true;
                                    $item->description = "";
                                    $item->save();
                                    $i++;
                                }
                            }
                             echo "Saved playlist for channel id $id with ".($i + 1)." items".PHP_EOL;
                        }
                    }
                }
            }
        }
    }
}
