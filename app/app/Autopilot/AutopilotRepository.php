<?php
namespace App\Autopilot;
use App\Models\Channel;
use App\Helpers\VideoServerAPI;
use App\Models\Like;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class AutopilotRepository {

    public static function getServersList() {
        return [
            'v1.catcast.tv',
            'v2.catcast.tv',
            'v3.catcast.tv',
            'eurodance90.catcast.tv',
            'kb.catcast.tv',
          //  'marstv.catcast.tv',
            'tvshans.catcast.tv',
            'tis.catcast.tv'
        ];
    }

    public static function getAllServersList() {
        return [
            'v1.catcast.tv',
            'v2.catcast.tv',
            'v3.catcast.tv',
            'eurodance90.catcast.tv',
            'kb.catcast.tv',
            //  'marstv.catcast.tv',
            'tvshans.catcast.tv',
            'tis.catcast.tv',
            'kino-1.catcast.tv',
            'freshtv.catcast.tv'
        ];
    }

    public static function getAll() {
        $autopilots = Cache::get('autopilot_list', function () {
            $data = [];
            $playlists = AutopilotPlaylist::all();
            $playlists_by_channel = [];
            foreach ($playlists as $playlist) {
                if (!isset($playlists_by_channel[$playlist->channel_id])) {
                    $playlists_by_channel[$playlist->channel_id] = [];
                }
                $playlists_by_channel[$playlist->channel_id][] = $playlist;
            }
            $autopilots = [];
            foreach ($playlists_by_channel as $channel_id => $contents) {
                $collection = new AutopilotCollection($contents, $channel_id);
                $autopilots[] = $collection;
                if ($collection->isOnline()) {
                    $data[$channel_id] = $contents;
                }
            }
            $GLOBALS['autopilot_file_data'] = $data;
            Cache::put('autopilot_file_data',$data,10);
            return $autopilots;
        });
        return $autopilots;
    }

    public static function getAllPlaylists() {
        $playlists = AutopilotPlaylist::all();
        return $playlists;
    }

    public static function getForChannelId($id) {
        if (isset( $GLOBALS['autopilot_file_data'])) {
            $autopilot_file_data = $GLOBALS['autopilot_file_data'];
        } else {
            $autopilot_file_data = Cache::get('autopilot_file_data');
        }
        if ($autopilot_file_data && isset($autopilot_file_data[$id])) {
            $data = $autopilot_file_data[$id];
        } else {
           $data = AutopilotPlaylist::where(['channel_id' => $id])->get();
        }

        $collection = new AutopilotCollection($data, $id);
        return $collection;
    }

    public static function getViewers($id) {
        //if (request()->filled('debug')) {
            $viewers_data = Cache::get('autopilot_viewers_data');
            if (!$viewers_data) {
                $viewers_data = [];
                foreach (static::getServersList() as $server) {
                    $statistics = file_get_contents("http://$server/statistics.php");
                    $statistics = json_decode($statistics);
                    if ($statistics->status) {
                        foreach ($statistics->data as $channel_id => $channel_data) {
                            $viewers_data[$channel_id] = $channel_data->count;
                        }
                    }
                }
                Cache::put('autopilot_viewers_data', $viewers_data, 5);
            }
            if (isset($viewers_data[$id])) {
                return $viewers_data[$id];
            }
            return 0;
        //}
        //return 0;
    }

    public static function getViewersListForChannel($server, $id) {
        $list = [];
        try {
            $options = stream_context_create(['http'=>
                [
                    'timeout' => 5
                ]
            ]);
            $statistics = file_get_contents("http://$server/statistics.php", false, $options);
            $statistics = json_decode($statistics);
            if (isset($statistics->data)) {
                foreach ($statistics->data as $channel_id => $channel_data) {
                     if ($id == $channel_id) {
                        $list = $channel_data->list;
                    }
                }
            }
        } catch (\Exception $e) {
            return [];
        }
        return $list;
    }

    public static function getViewersList($id) {
        $list = [];
        $servers = ['v1.catcast.tv', 'v2.catcast.tv', 'v3.catcast.tv'];
        if (VideoServerAPI::getSpecialServer($id)) {
            $servers = [VideoServerAPI::getSpecialServer($id)];
        }
        foreach ($servers as $server) {
            $list = array_merge($list, self::getViewersListForChannel($server, $id));
        }
        return $list;
    }

    public static function getAllViewersList() {
        $list = [];
        foreach (static::getAllServersList() as $server) {
            try {
                $statistics = file_get_contents("http://$server/statistics.php");
                $statistics = json_decode($statistics);
                if ($statistics->status) {
                    foreach ($statistics->data as $channel_id => $channel_data) {
                        if (!isset($list[$channel_id])) {
                            $list[$channel_id] = [];
                        }
                        $list[$channel_id] = array_merge($list[$channel_id], $channel_data->list);

                    }
                }
            } catch(\Exception $e) {

            }
        }
        return $list;
    }


    public static function getNext($time, $min_length, $only_liked) {
        $all = self::getAllPlaylists();
        $all = self::filterExisting($all);
        $items = [];
        $playlist_ids = [];
        if ($only_liked) {
            if ($user = auth()->user()) {
                $liked_channel_ids = Like::where(['id' => $user->id, 'entity_type' => 'channels'])->get()->pluck('entity_id');
            } else {
                $liked_channel_ids = [];
            }
        }
        $hide_timetable = Channel::where(['hide_timetable' => true])->pluck('id');
        $hide_timetable_playlists = AutopilotPlaylist::whereIn('channel_id', $hide_timetable)->pluck('id');
        if ($only_liked) {
            $playlist_ids = AutopilotPlaylist::whereIn('channel_id', $liked_channel_ids)->whereNotIn('channel_id', $hide_timetable);
        } else{
            $playlist_ids = AutopilotPlaylist::whereNotIn('channel_id', $hide_timetable);

        }

        $playlist_ids = $playlist_ids->pluck('id');
        $playlist_items = AutopilotItem::whereIn('playlist_id', $playlist_ids)->whereNotIn('playlist_id', $hide_timetable_playlists)->orderBy('index','ASC')->get();
        $playlist_folders = AutopilotFolder::whereIn('playlist_id', $playlist_ids)->whereNotIn('playlist_id', $hide_timetable_playlists)->orderBy('index','ASC')->get();


        $items_by_id = [];
        foreach ($playlist_items as $playlist_item) {
            if (!isset($items_by_id[$playlist_item->playlist_id])) {
                $items_by_id[$playlist_item->playlist_id] = [$playlist_item];
            } else {
                $items_by_id[$playlist_item->playlist_id][] = $playlist_item;
            }
        }
        foreach ($playlist_folders as $playlist_folder_item) {
            if (!isset($items_by_id[$playlist_folder_item->playlist_id])) {
                $items_by_id[$playlist_folder_item->playlist_id] = [$playlist_folder_item];
            } else {
                $items_by_id[$playlist_folder_item->playlist_id][] = $playlist_folder_item;
            }
        }
        foreach ($items_by_id as $playlist_id => $playlist_items) {
            usort($items_by_id[$playlist_id], function($a, $b)
            {
                return $a->index > $b->index;
            });
        }

        foreach ($all as $playlist) {
            if (isset( $items_by_id[$playlist->id])) {
                $playlist->extended_data->setItemsWithFolders($items_by_id[$playlist->id]);
                $items = array_merge($items, $playlist->extended_data->getNext($time, $min_length));
            }
        }
        $items = collect($items)->sortBy('time');
        return $items;
    }

    public static function getNextForChannel($channel_id, $time, $min_length) {
        $hide_timetable = Channel::find($channel_id)->hide_timetable;
        if ($hide_timetable) {
            return [];
        }
        $all =  AutopilotPlaylist::where(['channel_id' => $channel_id])->get();
        $items = [];
        $playlist_ids = $all->pluck('id');
        $playlist_items = AutopilotItem::whereIn('playlist_id', $playlist_ids)->orderBy('index','ASC')->get();
        $playlist_folders = AutopilotFolder::whereIn('playlist_id', $playlist_ids)->orderBy('index','ASC')->get();

        $items_by_id = [];
        foreach ($playlist_items as $playlist_item) {
            if (!isset($items_by_id[$playlist_item->playlist_id])) {
                $items_by_id[$playlist_item->playlist_id] = [$playlist_item];
            } else {
                $items_by_id[$playlist_item->playlist_id][] = $playlist_item;
            }
        }
        foreach ($playlist_folders as $playlist_folder_item) {
            if (!isset($items_by_id[$playlist_folder_item->playlist_id])) {
                $items_by_id[$playlist_folder_item->playlist_id] = [$playlist_folder_item];
            } else {
                $items_by_id[$playlist_folder_item->playlist_id][] = $playlist_folder_item;
            }
        }
        foreach ($items_by_id as $playlist_id => $playlist_items) {
            usort($items_by_id[$playlist_id], function($a, $b)
            {
                return $a->index > $b->index;
            });
        }

        foreach ($all as $playlist) {
            if (isset( $items_by_id[$playlist->id])) {
                $playlist->extended_data->setItemsWithFolders($items_by_id[$playlist->id]);
                $items = array_merge($items, $playlist->extended_data->getForTimeInterval($time, $time + 60 * 60 * 24, $min_length));
            }
        }
        $items = collect($items)->sortBy('time');
        return $items;
    }

    public static function filterExisting($collections) {
        $items = [];
        $ids = Channel::whereNull('blocked_at')->pluck('id');
        foreach ($collections as $collection) {
            if ($ids->contains($collection->channel_id)) {
                $items[] = $collection;
            }
        }
        return $items;
    }

    public static function filterLiked($items) {
        $new_list = [];
        if ($user = auth()->user()) {
            $liked = Like::where(['id'=>$user->id, 'entity_type'=>'channels'])->get()->pluck('entity_id');
            foreach ($items as $item) {
                if ($liked->contains($item->channel_id)) {
                    $new_list[] = $item;
                }
            }
        }
        return $new_list;
    }

}
