<?php

namespace App\Console\Commands;

use App\Helpers\MediaHelper;
use App\Models\Channel;
use Illuminate\Console\Command;

class UpdateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'thumbnails:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update thumbnails for live channels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ffmpeg = MediaHelper::createFFMpeg();
        $channels = Channel::filterOnline()->get();
        foreach ($channels as $channel) {
            $ffmpeg->getFFMpegDriver()->command([
                '-y',
                '-i',
                $channel->activeBroadcast->internal_rtmp_url,
                '-vcodec',
                'png',
                '-vframes',
                '1',
                '-s',
                '640x360',
                '-an', public_path('thumbnails/'.$channel->id.'.png')
            ]);
        }
    }
}
