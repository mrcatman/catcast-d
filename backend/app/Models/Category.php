<?php
namespace App\Models;
use App\Traits\HasPictures;
use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    use HasPictures;

    protected $guarded = [];
    protected $appends = [
        'pictures_data',
        'picture',
    ];

    public $pictures_fields = ['picture'];

    public static function getEntityType() {
        return 'categories';
    }


    public function scopeSearch($query) {
        return $query->where('name', 'LIKE', '%'.request()->input('search').'%');
    }

    public function scopeFilterPopular($query) {
        return $query->withCount('broadcasts')->orderBy('broadcasts_count', 'desc');
    }

    public function scopeFilterPopularOnline($query) {
        return $query->whereHas('online_broadcasts')->withCount('online_broadcasts')->orderBy('online_broadcasts_count', 'desc');
    }

    public function broadcasts() {
        return $this->hasMany(Broadcast::class, 'category_id');
    }

    public function online_broadcasts() {
        return $this->broadcasts()->filterOnline();
    }

    public function getPictureAttribute() {
        return $this->getPicture('picture', false);
    }


}
