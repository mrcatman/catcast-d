<?php
namespace App\Autopilot;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AutopilotPlaylist extends Model {

    public $table = "autopilot_playlists";

    protected $items = [];
    protected $guarded = [];

    protected $casts = [
        'data' => 'object'
    ];

    protected $_extended_data;

    protected $appends = ['extended_data', 'items'];

    public function getExtendedDataAttribute() {
        if (!$this->_extended_data) {
            $this->_extended_data =  new AutopilotPlaylistData($this);
        }
        return $this->_extended_data;
    }

    public function getItemsAttribute() {

        $videos = $this->hasMany(AutopilotItem::class, 'playlist_id')->whereNull('folder_id')->orderBy('index', 'ASC')->get();

        $folders = $this->hasMany(AutopilotFolder::class, 'playlist_id')->orderBy('index', 'ASC')->get();
        $items = $videos->merge($folders)->sortBy('index');
        $items = $items->values();

        return $items;
    }

    public function channel() {
        return $this->belongsTo(Channel::class);
    }

}
