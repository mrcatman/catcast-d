<?php

namespace App\Console\Commands;


use App\Helpers\StatisticsHelper;
use Illuminate\Console\Command;

class updateViewers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'viewers:update';

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
        BroadcastServers::updateViewersCount();
        StatisticsHelper::updateOnlineViewers();
    }
}
