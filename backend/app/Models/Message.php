<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasAttachments;

class Message extends Model
{
    use HasAttachments;

    protected $dateFormat = 'd.m.Y H:i';

    protected $table = 'messages';
    protected $hidden = ['delete_poster','delete_receiver'];

    protected $with = ['attachments'];


    public $timestamps = false;

    protected $attachments_entity_type = "message";


    public function from() {
        return $this->belongsTo('App\Models\User','from_id','id');
    }

    public function to() {
        return $this->belongsTo('App\Models\User','to_id','id');
    }

    public function user() {
        $user_id = auth()->user()->id;
        if ($this->from_id == $user_id) {
            return $this->belongsTo('App\Models\User','to_id','id');
        } else {
            return $this->belongsTo('App\Models\User','from_id','id');
        }
    }

    public function getDateAttribute() {
        return date($this->dateFormat,$this->attributes['time']);
    }

    public function getIsEditedAttribute() {
       return $this->edited_at && $this->edited_at > 0;
    }

    public function getEditDateAttribute() {
        return date($this->dateFormat,$this->edited_at);
    }
}
