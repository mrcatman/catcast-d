<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\NewsItem;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedController extends Controller {

    public function index()
    {

        $count = request()->input('count', 10);
        $page = request()->input('page', 1);
        $types_data = request()->input('types', []);
        $types = [
            'news' => isset($types_data['news']) && $types_data['news'],
            'videos' => isset($types_data['videos']) && $types_data['videos'],
            'posts' => isset($types_data['posts']) && $types_data['posts']
        ];

        $total = 0;
        $data = collect([]);
        if ($types['news']) {
            $news = NewsItem::fromLikedChannels()->orderBy('created_at', 'DESC')->get();
            $total += $news->count();
            $news = $news->map(function ($news_item) {
                $news_item->is_news = true;
                $news_item->is_comment = false;
                $news_item->load('channel');
                return [
                    'type' => 'news',
                    'data' => $news_item
                ];
            });
            $data = $data->merge($news);
        }
        if ($types['videos']) {
            $videos = Media::visible()->fromLikedChannels()->orderBy('id', 'DESC')->get();
            $video_groups = [];
            $last_channel_id = -1;
            $collection = [];
            foreach ($videos as $video) {
                if ($video->channel_id == $last_channel_id) {
                    $collection[] = $video;
                } else {
                    if (count($collection) > 0) {
                        if ($collection[0]->channel) {
                            $video_groups[] =
                                [
                                    'add_time' => $collection[0]->add_time_readable,
                                    'channel' => $collection[0]->channel,
                                    'list' => $collection,
                                ];
                        }
                    }
                    $last_channel_id = $video->channel_id;
                    $collection = [];
                }
            }
            $video_groups = collect($video_groups);
            $total += $video_groups->count();
            $video_groups = $video_groups->map(function ($videos_item) {
                return [
                    'type' => 'videos',
                    'data' => $videos_item
                ];
            });
            $data = $data->merge($video_groups);
        }
        if ($types['posts']) {
            $posts = Comment::fromFriends()->orderBy('date', 'DESC')->get();
            $total += $posts->count();
            $posts = $posts->map(function ($posts_item) {
                $posts_item->is_news = false;
                $posts_item->is_comment = true;
                $posts_item->load('user:id,username,avatar');

                return [
                    'type' => 'posts',
                    'data' => $posts_item
                ];
            });
            $data = $data->merge($posts);
        }
        $data = $data->sortByDesc(function ($item, $key) {
            if (is_array($item['data'])) {
                return $item['data']['list'][0]->add_time_original;
            } else {
                return $item['data']->add_time_original;
            }

        })->values();
        $paginator = new LengthAwarePaginator($data->forPage($page, $count)->values(), $total, $count);
        return $paginator;
    }

}
