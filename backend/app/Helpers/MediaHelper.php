<?php

namespace App\Helpers;

use App\Events\Media\MediaDeletedEvent;
use App\Models\MediaFile;
use App\Models\MediaFolder;
use App\Models\Tag;
use App\Models\VideoGridThumbnail;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;


class MediaHelper {

    public static function getFFMpegConfig() {
        return [
            'ffprobe.binaries' => exec('which ffprobe'),
            'ffmpeg.binaries' => exec('which ffmpeg'),
        ];
    }

    /**
     * @return FFMpeg
     */
    public static function createFFMpeg() {
        return FFMpeg::create(self::getFFMpegConfig());
    }

    /**
     * @return FFProbe
     */
    public static function createFFProbe() {
        return FFProbe::create(self::getFFMpegConfig());
    }

    public static function getExternalInfo($video_url) {
        $command = 'youtube-dl -J '.$video_url;
        $output = json_decode(shell_exec($command));
        if (!$output) {
            throw new \Exception('dashboard.videos.errors.external_media_not_found');
        }

        //Cache::put('video_'.$data->type.'_'.$data->video_id, json_encode($data), 30);
        $data = [
            'title' => isset($output->title) ? $output->title : '',
            'description' => isset($output->description) ? $output->description : '',
            'thumbnail' => isset($output->thumbnail) ? $output->thumbnail : '',
        ];
        return $data;
    }

    public static function generateThumbnail($path, $thumbnail_path, $seconds = 1) {
        $command = 'ffmpeg -y -i '.$path.' -ss '.$seconds.' -vframes 1 '.$thumbnail_path.' 2>&1';
        shell_exec($command);
        return file_exists($thumbnail_path);
    }

    public static function generateGridThumbnail($path, $thumbnail_path, $duration) {
        $video_data = shell_exec('ffprobe -v 0 -of csv=p=0 -select_streams v:0 -show_entries stream=r_frame_rate,width,height '.$path);
        $video_data = explode(',',$video_data);
        $fps_data = explode("/",$video_data[2]);
        $fps = ceil((int)$fps_data[0] / (int)$fps_data[1]);
        $every_nth_second = 2;
        if ($duration > 30) {
            $every_nth_second = 5;
        }
        if ($duration > 120) {
            $every_nth_second = 10;
        }
        if ($duration > 600) {
            $every_nth_second = 30;
        }
        $frames_count = floor($duration / $every_nth_second);
        $every_nth_frame = $fps *  $every_nth_second;
        $height = 200;
        $command = 'ffmpeg -i '.$path.' -vf "select=not(mod(n\,'.$every_nth_frame.')),scale=-1:'.$height.',tile='.$frames_count.'x1" -vsync 0 '.$thumbnail_path;
        shell_exec($command);

        $width = ceil((int)$video_data[0] * ($height / (int)$video_data[1]));

        return file_exists($thumbnail_path) ? [
            'width' => $width,
            'height' => $height,
            'every_nth_second' =>  $every_nth_second,
            'frames_count' => $frames_count,
        ] : null;
    }

    // todo: move
    public static function getSubFolders($id) {
        $id = (int)$id;
        $all_ids = [$id];
        $folders = MediaFolder::where(['parent_id' => $id])->get();
        foreach ($folders as $folder) {
            $all_ids[] = $folder->id;
            $sub = self::getSubFolders($folder->id);
            foreach ($sub as $sub_id) {
                if (!in_array($sub_id, $all_ids)) {
                    $all_ids[] = $sub_id;
                }
            }
        }
        return $all_ids;
    }

    public static function deleteMedia($media) {
        if (!PermissionsHelper::getStatus(['media'], $media->channel, $media)) {
            return;
        }
        MediaFile::where(['media_id' => $media->id])->delete();
        VideoGridThumbnail::where(['video_id' => $media->id])->delete();

        broadcast(new MediaDeletedEvent(['id' => $media->id, 'channel_id' => $media->channel_id]));
        $media->delete();
    }

    // todo: move to FiltersHelper
    public static function filterAndSort($media) {
        $media = $media->fromNotBlockedChannels()->ofSelectedType()->orderBy('created_at', 'desc');
        if (request()->filled('search')) {
            $media = $media->search(request()->input('search'));
        }
        if (request()->filled('tags')) {
            $tags = explode(',', request()->input('tags'));
            foreach ($tags as &$tag) {
                $tag = trim($tag);
            }
            $tag_ids = Tag::where(['entity_type'=>'media'])->whereIn('tag', $tags)->pluck('entity_id');
            $media = $media->whereIn('id', $tag_ids);
        }

        $media = $media->paginate(request()->input('count', 24));
        $media->getCollection()->transform(function($media_item) {
            $media_item->load('channel:id,name,shortname,channel_type');
            return $media_item;
        });
        return $media;
    }

    // todo: move
    public static function getPermissions($media) {
        $user = auth()->user();
        if (!$user) {
            return [
                'can_edit' => false,
                'can_view_statistics' => false
            ];
        }
        $has_full_permissions = PermissionsHelper::getStatus(['media'], $media->channel, $media, true);
        $has_statistics_permissions = PermissionsHelper::getStatus(['statistics'], $media->channel);
        $can_edit = $has_full_permissions || $media->user_id == $user->id;
        $can_view_statistics = $has_statistics_permissions && $can_edit;
        return [
            'can_edit' => $can_edit,
            'can_view_statistics' => $can_view_statistics
        ];

    }


}
