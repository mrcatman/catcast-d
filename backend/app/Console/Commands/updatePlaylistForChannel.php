<?php

namespace App\Console\Commands;

use App\Autopilot\AutopilotPlaylistGenerator;
use App\Autopilot\AutopilotTempItem;
use App\Helpers\VideoServerAPI;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class updatePlaylistForChannel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'playlists:test';

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
        AutopilotPlaylistGenerator::generateAll(['verbose' => true]);
    }
}
