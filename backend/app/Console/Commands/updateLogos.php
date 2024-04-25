<?php

namespace App\Console\Commands;

use App\Autopilot\AutopilotPlaylistGenerator;
use App\Autopilot\AutopilotTempItem;
use App\Models\Channel;
use App\Models\Logo;
use App\Models\Picture;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class updateLogos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logos:update';

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
        $files = glob(public_path("data/logos*"));
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file));
            $channel_logos = [];
            if ($data->current && $data->current->url != "") {
                $channel_logos[] = $data->current->url;
            }
            if (isset($data->items) && is_array($data->items)) {
                foreach ($data->items as $item) {
                    if ($item->src && $item->src != "") {
                        $channel_logos[] = $item->src;
                    }
                }
            }
            preg_match('/\/home\/admin\/web\/dev.myowntv.org\/public_html\/public\/data\/logos_(.*?).json/', $file, $file_name_data);
            $channel_id = $file_name_data[1];
            $channel = Channel::find($channel_id);
            $index = 0;
            if ($channel) {
                foreach ($channel_logos as $channel_logo) {
                    $channel_logo = str_replace("http://i.myowntv.org/", "", $channel_logo);
                    if (!Str::startsWith($channel_logo, "http")) {
                        $path = explode("/", $channel_logo);
                        if (count($path) == 1) {
                            $folder = "";
                            $filename = $path[0];
                        } else {
                            $folder = $path[0];
                            $filename = $path[1];
                        }
                        $user_id = $channel->user_id;
                        $picture = Picture::where(['folder' => $folder, 'filename' => $filename])->first();
                        if (!$picture) {
                            $picture = new Picture([
                                'domain' => "img.catcast.tv",
                                'folder' => $folder,
                                'filename' => $filename,
                                'has_https' => true,
                                'user_id' => $user_id
                            ]);
                            $picture->save();
                        }
                        $logo = new Logo([
                            'channel_id' => $channel->id,
                            'user_id' => $user_id,
                            'is_active' => false,
                            'index' => $index,
                            'picture_id' => $picture->id
                        ]);
                        $logo->save();
                        echo "Added logo for channel ".$channel->name;

                        $index++;
                    }
                }
            }
        }

    }
}
