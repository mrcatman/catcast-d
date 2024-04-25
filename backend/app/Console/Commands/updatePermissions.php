<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class updatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:update';

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
        $i = 0;
        $permissions = \App\UserInteractPermissionsOld::all();
        foreach ($permissions as $right) {
            $permission = new \App\UserInteractPermissions();
            $permission->user_id = (int)$right->user;
            $permission->channel_id = (int)$right->channelid;
            $permission->is_confirmed = true;
            $permission->is_hidden = false;
            $permission->position = $right->position;
            $list = [];
            if ($right->poster && $right->moder && $right->broadcaster) {
                $list[] = 'channel_admin';
            } else {
                $list[] = 'statistics';
                if ($right->poster) {
                    $list[] = 'news';
                }
                if ($right->moder) {
                    $list[] = 'moderation';
                }
                if ($right->broadcaster) {
                    $list[] = "playlists";
                    $list[] = 'records';
                    $list[] = 'live_broadcast';
                    $list[] = 'autopilot';
                }
            }
            $i++;
            $permission->list = $list;
            echo "Added permission for channel ".$permission->channel_id.", user: ".$permission->user_id.", list: ".json_encode($list).PHP_EOL;
            $permission->save();
        }
        echo "Total count: ".$i;
    }
}
