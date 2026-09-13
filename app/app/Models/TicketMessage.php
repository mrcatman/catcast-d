<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    use HasAttachments;

    public $table = "tickets_messages";

    protected $attachments_entity_type = "ticket_message";
}
