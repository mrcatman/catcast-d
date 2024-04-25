<?php
namespace App\Models;
use App\Traits\HasAttachments;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Parsedown;

class Comment extends Model
{
    use SoftDeletes;
    use HasAttachments;

    protected $table = 'comments';
    protected $attachments_entity_type = "comment";
    protected $appends = ['display_text'];

    public static function getEntityType() {
        return 'comments';
    }


    public function getDisplayTextAttribute() {
        return app()->make(Parsedown::class)->setBreaksEnabled(true)->text($this->text);
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

    public function scopeFromFriends($query) {
        if ($user = auth()->user()) {
            $friends_from = Like::where(['id'=>$user->id,'entity_type'=>'friends'])->get()->pluck('entity_id');
            $friends_to = Like::where(['entity_id'=>$user->id,'entity_type'=>'friends'])->get()->pluck('id');
            $users = User::whereIn('id', $friends_from)->get();
            $users = $users->filter(function($user) use ($friends_to) {
                return $user->checkPrivacySettings('can_view_profile') && $user->checkPrivacySettings('can_write_on_wall');
            });
            return $query->where(['entity_type' => 'users'])->whereIn('id', $users->pluck('id'));
        }
        return $query;
    }
}
