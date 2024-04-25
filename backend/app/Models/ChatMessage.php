<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model {

    protected $hidden = ['ip_address'];

    protected $guarded = [];

    protected $casts = [
        'reply_to' => 'array'
    ];

    public function getColorAttribute($color) {
        if (isset($color[0]) && $color[0] !== '#') {
            $color = '#'.$color;
        }
        return $color;
    }



    public function getMessageTextAttribute($text) {
        $url = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
       // $text = preg_replace($url, '<a href="$0" target="_blank" title="$0">$0</a>', $text);
      //  $text = str_replace('img class="smiley" src="/s/','img class="smiley" src="https://img.catcast.tv/smileys/', $text);
        return $text;
    }

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
