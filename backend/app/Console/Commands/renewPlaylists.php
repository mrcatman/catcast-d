<?php

namespace App\Console\Commands;

use App\Autopilot\AutopilotPlaylistGenerator;
use App\Autopilot\AutopilotTempItem;
use Illuminate\Console\Command;

class renewPlaylists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'playlists:renew';

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
        //AutopilotTempItem::where('id', '>', '0')->delete();
        AutopilotPlaylistGenerator::generateAll(['verbose' => true, 'remove_old' => true]);
    }
}
