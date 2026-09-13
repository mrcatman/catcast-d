<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\HasAttachments;

class NewsItem extends Model
{
    use SoftDeletes;

    use HasAttachments;

    protected $dateFormat = 'd.m.Y H:i';


    protected $table = 'news';

    protected $hidden = ['created_at','newstext','newsid'];

    protected $appends = ['id','add_time','add_time_original','text', 'channel_id'];

    public $timestamps = false;

    protected $attachments_entity_type = "news";

    public function getIdAttribute()
    {
        return isset($this->attributes['newsid']) ? $this->attributes['newsid'] : -1;
    }

    public function getTextAttribute() {
        $text =  $this->attributes['newstext'];
        $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
        $text = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $text);
        return $text;
    }

    public function getChannelIdAttribute() {
        return $this->attributes['channelid'];
    }

    public function setChannelIdAttribute($channel_id) {
        $this->attributes['channelid'] = $channel_id;
        unset($this->attributes['channel_id']);
    }

    public function setTextAttribute($text) {
        $this->attributes['newstext'] = $text;
    }

    public function channel() {
        return $this->belongsTo('App\Models\Channel','channelid','id');
    }

    public function getAddTimeAttribute() {
        return date($this->dateFormat,$this->attributes['created_at']);
    }

    public function getAddTimeOriginalAttribute() {
        return date($this->attributes['created_at']);
    }

    public function scopeFromLikedChannels($query) {
        if ($user = auth()->user()) {
            $channels = Like::where(['id'=>$user->id,'entity_type'=>'channels'])->get()->pluck('entity_id');
            return $query->whereIn('channelid', $channels);
        }
        return $query;
    }
}
