<?php

namespace App\Notifications\Types;
use App\Helpers\LocalizationHelper;

use App\Models\Broadcast;
use App\Models\Comment;

use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use NotificationChannels\Telegram\TelegramMessage;
use App\Notifications\Channels\VK\Message as VKMessage;

class ChannelNewPost extends BaseNotificationType {

    protected $default_channels = [];

    private $comment;

    public function __construct(Comment $comment) {
        $this->comment = $comment;
    }

    public static function getEventTypeId() {
        return 'new_post';
    }

    public static function getEntityClass() {
        return Comment::class;
    }

    public function getEntity() {
        return $this->comment;
    }

    public function getTitle() {
        return LocalizationHelper::translate('notifications.types.new_post.heading_full', [
            'channel_name' => $this->comment->channel->name,
            'title' => $this->comment->title
        ]);
    }

    public function getLink() {
        return "/".$this->comment->channel->shortname."?comment_id=".$this->comment->id.'&t=wall';
    }

    public function filterUsers(Collection $users) {
        return $users;
        return $users->filter(function($user) {
            return $user['user']->id != $this->comment->user_id;
        });
    }

    public function toMail() {
        return (new MailMessage)->subject($this->getTitle())->view('emails.channel_new_feed_post', [
            'comment' => $this->comment,
            'url' => $this->getFullURL(),
        ]);
    }

    public function toBroadcast($notifiable) {
        return new BroadcastMessage([
            'title' => $this->getTitle(),
            'picture' => $this->comment->channel->logo,
            'text' => $this->comment->text,
            'url' => $this->getRelativeURL(),
        ]);
    }

}
