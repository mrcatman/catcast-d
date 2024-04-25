<?php

namespace App\Console\Commands;

use App\Models\Channel;
use Illuminate\Console\Command;

class syncLikes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'likes:sync';

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
        foreach (Channel::all() as $item) {
            $item->likes_count = count($item->likes);
            echo $item->id.": ".$item->likes_count." likes".PHP_EOL;
            $item->save();
        }
    }
}
