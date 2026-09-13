<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public $table = "tickets";

    protected $casts = [
        'connected_page' => 'object',
    ];

    protected $appends = ['connected_page_title', 'unread_messages', 'status'];

    public function user() {
        return $this->belongsTo('App\Models\User');
    }

    public function getConnectedPageTitleAttribute() {
        if ($this->connected_page && isset($this->connected_page->type)) {
            $id = $this->connected_page->id;
            $title = '';
            switch ($this->connected_page->type) {
                case 'channels':
                    $channel = Channel::find($id);
                    if ($channel) {
                        $title = $channel->name;
                    }
                    break;
                case 'videos':
                    $video = Media::find($id);
                    if ($video) {
                        $title = $video->title;
                    }
                    break;
                default:
                    break;
            }
            return $title;
        }
        return '';
    }

    public function getUnreadMessagesAttribute() {
        return TicketMessage::where(['ticket_id' => $this->id,'is_answer' => true, 'is_read' => false])->count();
    }

    public function getLastMessageAttribute() {
        return TicketMessage::where(['ticket_id' => $this->id])->orderBy('created_at', 'DESC')->first();
    }

    public function getStatusAttribute() {
        if ($this->is_closed) {
            return "closed";
        }
        $last_message = $this->last_message;
        if ($last_message && $last_message->is_answer) {
            return "waiting_for_answer";
        } else {
            return "waiting_for_response";
        }
    }
}
