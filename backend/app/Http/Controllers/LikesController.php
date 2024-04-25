<?php

namespace App\Http\Controllers;

use App\Enums\PrivacyStatuses;
use App\Helpers\CommonResponses;
use App\Helpers\ConfigHelper;
use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Playlist;
use App\Models\Media;

class LikesController extends Controller
{

    public $classes = [
        'channels' => Channel::class,
        'comments' => Comment::class,
        'playlists' => Playlist::class,
        'media' => Media::class,
    ];


    protected $sync_classes = [
        'channels' => Channel::class,
        'media' => Media::class
    ];


    private function ratingEnabled($entity_type, $entity_id) {
        if ($entity_type === Media::getEntityType()) {
            $entity = Media::findOrFail($entity_id);
            return $entity->additional_settings['privacy']['rating_enabled'];
        }
        return true;
    }

    private function checkAccess($entity_type, $entity_id) {
        if (isset($this->classes[$entity_type])) {
            $entity = $this->classes[$entity_type]::findOrFail($entity_id);
            if (in_array($entity_type, [Media::getEntityType(), Playlist::getEntityType()])) {
                if ($entity->privacy_status === PrivacyStatuses::PRIVATE) {
                    return PermissionsHelper::getStatus(['records'], $entity->channel);
                } elseif ($entity->privacy_status === PrivacyStatuses::UNLISTED) {
                    return $entity->uuid = request()->input('uuid');
                }
            }
            return true;
        }
        return false;
    }

    public function set($entity_type, $entity_id) {
        if (!isset($this->classes[$entity_type])) {
            return CommonResponses::notFound();
        }
        if (!$this->checkAccess($entity_type, $entity_id)) {
            return CommonResponses::unauthorized();
        }
        $material = $this->classes[$entity_type]::findOrFail($entity_id);
        $user = auth()->user();
        $instance = Like::where([
            'entity_type' => $entity_type,
            'entity_id' => $material->id,
            'user_id' => $user->id
        ])->first();

        $state = !!request()->input('state');

        $can_dislike = ConfigHelper::enableDislikes($entity_type);
        if ($can_dislike) {
            $weight = request()->input('weight');
            if ($weight !== -1 && $weight !== 1) {
                $weight = 1;
            }
        } else {
            $weight = 1;
        }

        if ($instance && $state) {
            $instance->weight = $weight;
            $instance->save();
        } elseif (!$instance && $state) {
            $like = new Like([
                'entity_type' => $entity_type,
                'entity_id' => $material->id,
                'user_id' => $user->id
            ]);
            if ($can_dislike) {
                $like->weight = $weight;
            }
            $like->save();
        } elseif ($instance && !$state) {
            $instance->delete();
        }
        $new_likes = $this->getByMaterial($entity_type, $entity_id)['rating'];
        if (isset($this->sync_classes[$entity_type])) {
            $material->likes_count = $new_likes;
            $material->save();
        }
        return $this->getByMaterial($entity_type, $entity_id);
    }

    public function getByMaterial($entity_type, $entity_id){
        if (!isset($this->classes[$entity_type])) {
            return CommonResponses::notFound();
        }
        if (!$this->checkAccess($entity_type, $entity_id)) {
            return CommonResponses::unauthorized();
        }
        $list = Like::where([
            'entity_type' => $entity_type,
            'entity_id' => $entity_id
        ]);

        $rating_enabled = $this->ratingEnabled($entity_type, $entity_id);
        if ($rating_enabled) {
            $rating = (int)$list->clone()->sum('weight');
            $rating_positive = (int)$list->clone()->where('weight', '>', 0)->sum('weight');
            $rating_negative = (int)$list->clone()->where('weight', '<', 0)->sum('weight') * -1;
            $last_likes = ConfigHelper::showLikedUsers($entity_type) ? $list->with('user:id,username')->orderBy('updated_at', 'desc')->limit(5)->get() : null;
        } else {
            $rating = 0;
            $rating_positive = 0;
            $rating_negative = 0;
            $last_likes = [];
        }
        $data = [
            'rating_enabled' => $rating_enabled,
            'last_likes' => $last_likes,
            'rating' => $rating,
            'positive_rating' => $rating_positive,
            'negative_rating' => $rating_negative
        ];
        if ($user = auth()->user()) {
            $user_like = Like::where([
                'user_id' => $user->id,
                'entity_type' => $entity_type,
                'entity_id' => $entity_id
            ])->first();
            $data['current_user_has_liked'] = !!$user_like;
            if ($user_like) {
                $data['current_user_like_weight'] = $user_like->weight;
            }
        }
        return $data;
    }

    public function getUsersByMaterial($entity_type, $entity_id) {
        if (!ConfigHelper::showLikedUsers($entity_type) || !$this->ratingEnabled($entity_type, $entity_id) || !$this->checkAccess($entity_type, $entity_id)) {
            return CommonResponses::unauthorized();
        }
        if (!isset($this->classes[$entity_type])) {
            return CommonResponses::notFound();
        }
        $list = Like::with('user:id,username')->where([
            'entity_type' => $entity_type,
            'entity_id' => $entity_id
        ])->orderBy('updated_at', 'desc')->paginate(request()->input('count', 20));
        return $list;
    }

}
