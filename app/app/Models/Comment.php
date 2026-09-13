<?php
namespace App\Models;
use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;
    use HasAttachments;

    protected $table = 'comments';
    protected $attachments_entity_type = "comment";

    public static function getEntityType() {
        return 'comments';
    }

    public function likes(){
        return $this->hasMany(Like::class, 'entity_id', 'id')->with('user:id,username')->where('entity_type','=','comments');
    }

    public function channel(){
        return $this->belongsTo(Channel::class, 'channel_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function children() {
        return $this->hasMany(Comment::class, 'entity_id', 'id')->where([
            'entity_type' => 'comments',
        ]);
    }
}
