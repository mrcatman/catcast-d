<?php

namespace App\Http\Controllers;

use App\Enums\PrivacyStatuses;
use App\Helpers\ConfigHelper;
use App\Helpers\CommonResponses;
use App\Helpers\PermissionsHelper;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Notification;
use App\Models\Playlist;
use App\Models\User;
use App\Models\UserBan;
use App\Models\Media;
use App\Notifications\Types\ChannelNewPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CommentsController extends Controller {

    private $entity_classes = [];

    public function __construct() {
        $this->entity_classes = [
            Channel::getEntityType() => Channel::class,
            Comment::getEntityType() => Comment::class,
            User::getEntityType() => User::class,
            Playlist::getEntityType() => Playlist::class,
            Media::getEntityType() => Media::class,
        ];
    }

    /**
     * Check if current user can comment on an entity
     * @param Model $entity
     * @return boolean
     */
    private function canComment($entity) {
        if ($this->hasPrivacySettings($entity::getEntityType())) {
            if ($entity->privacy_status === PrivacyStatuses::PRIVATE) {
                return PermissionsHelper::getStatus(['records'], $entity->channel);
            } elseif ($entity->privacy_status === PrivacyStatuses::UNLISTED) {
                return $entity->uuid = request()->input('uuid');
            }
        }
        return true;
    }

    /**
     * Check if current user can edit the comment
     * @param Comment $comment
     * @return boolean
     */
    private function canEdit($comment){
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->is_admin) {
            return true;
        }
        $author = $comment->user;
        if ($comment->entity_type == User::getEntityType() && $comment->entity_id == $user->id) {
            return true;
        }
        if ($author && $author->id === $user->id) {
            return true;
        }
        if ($comment->channel) {
            return PermissionsHelper::getStatus(['news'], $comment->channel);
        }
        return false;
    }

    /**
     * Check if an entity type has a privacy settings model
     * @param string $entity_type
     * @return boolean
     */
    private function hasPrivacySettings($entity_type) {
        return in_array($entity_type, [Media::getEntityType(), Playlist::getEntityType()]);
    }

    /**
     * Get the root entity in the comments tree
     * @param Comment $comment
     * @return Model
     */
    private function getRootEntity(Comment $comment) {
        $entity_id = $comment->entity_id;
        $entity_type = $comment->entity_type;
        if ($entity_type == Comment::getEntityType()) {
            $parent_id = $comment->entity_id;
            $need_find = true;
            while ($need_find) {
                $parent_comment = Comment::where(['id' => $parent_id])->first();
                if ($parent_comment) {
                    if ($parent_comment->entity_type == Comment::getEntityType()) {
                        $parent_id = $parent_comment->entity_id;
                    } else {
                        $entity_id = $parent_comment->entity_id;
                        $entity_type = $parent_comment->entity_type;
                        $need_find = false;
                    }
                } else {
                    $need_find = false;
                }
            }
        }
        return $this->entity_classes[$entity_type]::findOrFail($entity_id);
    }

    /**
     * Load the rating info of the comment
     * @param Comment $comment
     * @void
     */
    private function loadRating(Comment $comment) {
        $rating = (new LikesController())->getByMaterial(Comment::getEntityType(), $comment->id);
        $comment->rating = $rating;
    }

    /**
     * Load the children comments for the given one
     * @param Comment $comment
     * @param int|null $limit
     * @param int|null $after_id Last ID of already loaded child comment
     * @void
     */
    private function loadChildren(Comment $comment, int $limit = null, int $after_id = null) {
        $list = Comment::with(['user:id,username', 'likes', 'attachments'])->where([
            'entity_type' => 'comments',
            'entity_id' => $comment->id,
        ])->orderBy('id', 'ASC');
        $comment->children_count = $list->count();
        if ($after_id) {
            $list = $list->where('id', '>', $after_id);
        }
        if ($limit) {
            $list = $list->limit($limit);
        }
        $list = $list->get();
        $comment->children = $list; // TODO: move limit into config
        if (count($comment->children) > 0) {
            foreach ($comment->children as $child) {
                $child->can_edit = $this->canEdit($comment);
                $this->loadRating($child);
                $this->loadChildren($child, $limit);
            }
        }
    }


    public function show($entity_type, $entity_id) {
        if (!isset($this->entity_classes[$entity_type])) {
            return CommonResponses::notFound();
        }

        $list = Comment::with('attachments', 'user:id,username', 'channel:id,name,shortname')->where([
            'comments.entity_type' => $entity_type,
            'comments.entity_id' => $entity_id,
        ]);

        $order = request()->get('order');
        if ($order == 'old') { // todo: unify the sorting
            $list = $list->orderBy('created_at', 'asc');
        } elseif ($order == 'commented') {
            $list = $list->withCount(['children'])->orderBy('children_count', 'desc');
        } elseif ($order == 'popular') {
            $comments_table = with(new Comment())->getTable();
            $likes_table = with(new Like())->getTable();
            $list = $list->select('*', DB::raw("(SELECT COALESCE(SUM($likes_table.weight), 0) FROM $likes_table WHERE $likes_table.entity_type='comments' and $likes_table.entity_id = $comments_table.id) as rating_sum"))->orderBy('rating_sum', 'desc');
        } else {
            $list = $list->orderBy('created_at', 'desc');
        }

        if ($this->hasPrivacySettings($entity_type)) {
            $entity = $this->entity_classes[$entity_type]::findOrFail($entity_id);
            if (!$this->canComment($entity)) {
                return CommonResponses::unauthorized();
            }
            if (!$entity->additional_settings['privacy']['comments_display']) {
                $list = $list->whereRaw('TRUE = FALSE'); // todo: create an empty paginator here
            }
        } elseif ($entity_type === User::getEntityType()) {
            $user = User::findOrFail($entity_id);
            if (!$user->checkPrivacySettings('can_view_profile')) {
                $list = $list->whereRaw('TRUE = FALSE');
            }
        }

        $list = $list->paginate(request()->input('count', 20));

        $limit = ConfigHelper::commentsMaxChildrenToLoad();
        $list->getCollection()->transform(function($comment) use ($limit) {
            $comment->can_edit = $this->canEdit($comment);
            $this->loadRating($comment);
            $this->loadChildren($comment, $limit);
            return $comment;
        });
        return $list;
    }


    public function getChildren($id) {
        $comment = Comment::findOrFail($id);
        $entity = $this->getRootEntity($comment);
        $entity_type = $entity::getEntityType();

        $channel = null;
        if ($entity_type === Channel::getEntityType()) {
            $channel = $entity;
        } elseif ($this->hasPrivacySettings($entity_type)) {
            $channel = Channel::findOrFail($entity->channel_id);
        }
        if ($channel) {
            if (!$channel->additional_settings['privacy']['comments_display']) {
                return CommonResponses::unauthorized();
            }
            if ($this->hasPrivacySettings($entity_type) && !$entity->additional_settings['privacy']['comments_display']) {
                return CommonResponses::unauthorized();
            }
        }
        if ($entity->ban) {
            return CommonResponses::unauthorized();
        }
        $after_id = request()->input('after_id');
        $this->loadChildren($comment, null, $after_id);
        return $comment->children;
    }

    public function update($id){
        $comment = Comment::findOrFail($id);
        if (!$this->canEdit($comment)) {
            return CommonResponses::unauthorized();
        }
        if (!request()->filled('text')) {
            return response()->json(['message' => 'comments.errors.enter_text'], 422);
        }
        $comment->text = strip_tags(request()->input('text'));
        if (request()->input('from_channel_name') && request()->filled('title')) {
            PermissionsHelper::check( ['news'], $comment->channel);
            $comment->title = request()->input('title', '');
        }
        $comment->save();
        if (request()->has('attachments')) {
            $comment->updateAttachments(request()->input('attachments'));
        }
        $comment->load(['user:id,username','attachments']);
        if ($comment->channel_id) {
            $comment->load(['channel:id,name,shortname']);
        }
        $comment->can_edit = true;
        return $comment;
    }

    public function restore($id) {
        $comment = Comment::withTrashed()->findOrFail($id);
        if (!$this->canEdit($comment)) {
            return CommonResponses::unauthorized();
        }
        $comment->restore();
        return ['message' =>'comments.comment_restored'];
    }

    public function destroy($id) {
        $comment = Comment::findOrFail($id);
        if (!$this->canEdit($comment)) {
            return CommonResponses::unauthorized();
        }
        $comment->delete();
        Notification::where(['entity_type' => Comment::getEntityType(), 'entity_id' => $comment->id])->delete();
        return ['message' => 'global.deleted'];
    }

    public function store() {
        $user = auth()->user();
        $comment = new Comment();
        $comment->user_id = auth()->user()->id;
        if (!request()->filled('text')) {
            return response()->json(['message' => 'comments.errors.enter_text'], 422);
        }
        $comment->text = strip_tags(request()->input('text'));
        if (!request()->filled('reply_to_comment_id')) {
            if (!isset($this->entity_classes[request()->input('entity_type')])) {
                return response()->json(['message' => 'errors.no_parameters'], 422);
            }
            $comment->entity_type = request()->input('entity_type');
            $entity = $this->entity_classes[request()->input('entity_type')]::findOrFail(request()->input('entity_id'));
            $comment->entity_id = $entity->id;
        } else {
            $reply_to_comment = Comment::find(request()->input('reply_to_comment_id'));
            if (!$reply_to_comment) {
                return response()->json(['message' => 'comments.errors.parent_comment_not_found'], 404);
            }
            $comment->entity_type = Comment::getEntityType();
            $comment->entity_id = $reply_to_comment->id;
        }
        $entity = $this->getRootEntity($comment);
        $entity_type = $entity::getEntityType();

        $channel = null;
        if ($entity_type == Channel::getEntityType()) {
            $channel = $entity;
        } elseif ($this->hasPrivacySettings($entity_type)) {
            $channel = Channel::findOrFail($entity->channel_id);
        }
        if ($channel) {
            $is_banned = UserBan::where(['channel_id' => $channel->id, 'user_id' => $user->id])->where(function($q) {
                $q->whereDate('banned_till','>=',Carbon::now());
                $q->orWhere(['banned_till' => null]);
            })->count() > 0;
            if ($is_banned) {
                return response()->json(['message' => 'comments.errors.you_are_banned'], 403);
            }
            if (!$channel->additional_settings['privacy']['comments_enabled']) {
                return CommonResponses::unauthorized();
            }
            if ($this->hasPrivacySettings($entity_type) && !$entity->additional_settings['privacy']['comments_enabled']) {
                return CommonResponses::unauthorized();
            }
            if (!$this->canComment($entity)) {
                return CommonResponses::unauthorized();
            }
        }

        $from_channel_name = false;

        if ($entity_type == User::getEntityType()) {
            if (!$entity->checkPrivacySettings('can_write_on_wall')) {
                return CommonResponses::unauthorized();
            }
        } elseif (request()->input('from_channel_name')) {
            $from_channel_name = true;
            PermissionsHelper::check( ['news'], $channel);
            $comment->channel_id = $channel->id;
            $comment->title = request()->input('title', '');
        }

        $comment->save();
        if (request()->has('attachments')) {
            $comment->updateAttachments(request()->input('attachments'));
        }
        $comment->load(['user:id,username','attachments']);
        if ($comment->channel_id) {
            $comment->load(['channel:id,name,shortname']);
        }
        $comment->can_edit = true;

        if ($from_channel_name) {
            (new ChannelNewPost($comment))->sendToChannelSubscribers($channel);
        }

        return $comment;
    }


    public function getAccessSettings($entity_type, $entity_id) {
        if (!isset($this->entity_classes[$entity_type])) {
            return CommonResponses::notFound();
        }
        if ($entity_type === 'channels') {
            $entity = $channel = Channel::findOrFail($entity_id);
        } else {
            // todo: check uuid for media/playlists
            $entity = $this->entity_classes[$entity_type]::findOrFail($entity_id);
            $channel = $entity->channel;
        }
        $permissions = PermissionsHelper::getForChannel($channel);
        $user = auth()->user();
        $ban = $user ? UserBan::where([
            'channel_id' => $channel->id,
            'user_id' => $user->id
        ])->where(function($q) {
            $q->whereDate('banned_till','>=',time());
            $q->orWhereNull('banned_till');
        })->first() : null;
        $comments_enabled = $entity->additional_settings['privacy']['comments_enabled'];
        $comments_display = $entity->additional_settings['privacy']['comments_display'];

        $comments_can_write_from_channel_name = PermissionsHelper::getStatus(['news'], $channel);

        return [
            'permissions' => $permissions,
            'ban' => $ban,
            'comments_enabled' => $comments_enabled,
            'comments_display' => $comments_display,
            'comments_can_write_from_channel_name' => $comments_can_write_from_channel_name,
        ];
    }

}

