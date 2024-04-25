<?php
namespace App\Autopilot;
class AutopilotPlaylistData implements \JsonSerializable {

    protected $playlist;

    public $time_start = 0;
    public $cycled = false;
    public $cycledTill = 0;
    public $items = null;
    public $items_with_folders = null;

    public $channel_id = null;
    public $playlist_id = null;
    public $title = '';
    public $valid = true;
    public $errors = [];
    public $playlistType;
    public $playbackType;
    public $playbackTrimType;
    public $playbackTimeStart;
    public $playbackDays = [];
    public $playbackLimitType;
    public $playbackLimitValue;
    public $playbackLimitValueMinutes;
    public $color = null;

    public function __construct($playlist = null) {

        if ($playlist && $playlist->data && is_object($playlist->data)) {
            $this->playlist = $playlist;
            $this->playlist_id = $playlist->id;
            $data = $playlist->data;

            if (!isset($data->start) && !isset($data->time_start)) {
                $this->time_start = time();
            } else {
                $this->time_start = (int)(isset($data->start) ? $data->start : $data->time_start);
            }
            $this->channel_id = (int)$playlist->channel_id;
            $this->title = isset($data->title) ? $data->title : '';
            $this->cycled = isset($data->cycled) ? ( isset($data->cycled->status) ? (bool)$data->cycled->status  : (bool)$data->cycled ) : false;
            if ($this->cycled) {
                if (isset($data->cycled->till)) {
                    $this->cycledTill = (int)$data->cycled->till;
                } else if (isset($data->cycledTill)) {
                    $this->cycledTill = (int)$data->cycledTill;
                } else {
                    $this->cycled = false;
                }
            }
            if (isset($data->type)) {
                $this->playlistType = $data->type;
            } else {
                $this->playlistType = "default";
            }
            if (isset($data->playback_type)) {
                $this->playbackType = $data->playback_type;
            }  else {
                $this->playbackType = "default";
            }
            if (isset($data->color)) {
                $this->color = $data->color;
            }

            $this->playbackTrimType = isset($data->playback_trim_type) ? $data->playback_trim_type : "default";

            if (isset($data->playback_time_start)) {
                if (isset($data->playback_time_start_minutes)) {
                    $this->playbackTimeStart = $data->playback_time_start_minutes;
                } else {
                    $playback_time_start = explode(":", $data->playback_time_start);
                    $minutes = $playback_time_start[0] * 60 + $playback_time_start[1];
                    $this->playbackTimeStart = $minutes;
                }
            }
            if (isset($data->playback_days)) {
                $this->playbackDays = $data->playback_days;
            }
            if (isset($data->playback_limit)) {
                $this->playbackLimitType = $data->playback_limit->type;
                $this->playbackLimitValue = $data->playback_limit->value;
                if ($data->playback_limit->type === "by_time") {
                    $this->playbackLimitValueMinutes = $data->playback_limit->value_minutes;
                }
            }
        }
    }

    public function getItems() {
       if (!$this->items) {
            $items = [];
            $playlist_items = $this->playlist->items;
            foreach ($playlist_items as $playlist_item) {
                if ($playlist_item instanceof AutopilotFolder) {
                    if($this->playlistType === "mixer") {
                        $folder_items = $playlist_item->getConnectedItems($this->playlist_id);
                    } else {
                        $folder_items = $playlist_item->items->sortBy('index');
                    }
                    foreach ($folder_items as $folder_item) {
                        $items[] = $folder_item;
                    }
                } else {
                    $items[] = $playlist_item;
                }
            }
            $this->items = collect($items);
        }
        return $this->items;
    }

    public function getItemsWithFolders() {
        if (!$this->items_with_folders) {

            if ($this->playlist) {
                $this->items_with_folders = $this->playlist->items;
            } else {
                $this->items_with_folders = [];
            }
        }
        return $this->items_with_folders;
    }

    public function getRandomItem() {
        $item = $this->getItems()->random();
        return $item;
    }

    public function setItemsWithFolders($items) {
        $this->items_with_folders = $items;
    }

    public function fromRequest($request) {


        $this->title = request()->input('data.title', '');
        $this->color = request()->input('data.color', '#fff');
        $this->playlistType = request()->input('data.type', 'default');
        if (!in_array($this->playlistType, ["default", "mixer"])) {
            $this->playlistType = "default";
        }
        $this->playbackType = request()->input('data.playback_type', 'default');
        if (!in_array($this->playbackType, ["default", "repeating", "around_the_clock"])) {
            $this->playbackType = "default";
        }
        $this->playbackTrimType = request()->input('data.playback_trim_type', 'default');
        if (!in_array($this->playbackTrimType, ["default", "move_next", "limit_prev"])) {
            $this->playbackTrimType = "default";
        }
        //$timezone_offset = (int)request()->header('x-timezone-offset');
        $timezone_offset = 0;
        if (!$timezone_offset) {
            $timezone_offset = 0;
        }
        $time_regex ="/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/";
        if ($this->playbackType === "default") {
            $time_start = request()->input('data.time_start');
            if (!$time_start || (int)$time_start <= 0) {
                $this->valid = false;
                $this->errors['time_start'] = ["scheduler.errors.enter_correct_date"];
            } else {
                $this->time_start = (int)$time_start; // + $timezone_offset;
           }
       }
        if ($this->playlistType === "default") {

            $this->cycled = request()->has('data.cycled.status') ? request()->input('data.cycled.status') : false;
            if ($this->cycled) {
                if (request()->has('data.cycled.till')) {
                    if ((int)request()->input('data.cycled.till') <= $this->time_start) {
                        $this->valid = false;
                        $this->errors['cycled_till'] = ["scheduler.errors.end_date_must_be_bigger_than_start"];
                    } else {
                        $this->cycledTill = (int)request()->input('data.cycled.till');
                    }
                } else {
                    $this->cycled = false;
                }
            }
        }
        if ($this->playbackType === "repeating") {
            if (!request()->has('data.playback_time_start') || !preg_match($time_regex, request()->input('data.playback_time_start'))) {
                $this->valid = false;
                $this->errors['playback_time_start'] = ["scheduler.errors.enter_correct_time"];
            } else {
                $playback_time_start_data = explode(":", request()->input('data.playback_time_start'));
                $minutes = $playback_time_start_data[0] * 60 + $playback_time_start_data[1] + $timezone_offset;

                if ($minutes < 0) {
                    $minutes = 3600 - $minutes;
                }

                //$hours = (int)floor($minutes / 60);
                //$minutes = ($minutes % 60);
                //$playback_time_start = ($hours).":".($minutes < 10 ? "0".$minutes : $minutes);
                $this->playbackTimeStart = $minutes;
            }
            $this->playbackDays = [];
            for ($i = 1; $i <= 7; $i++) {
                $this->playbackDays[$i] = $request->input('data.playback_days.'.$i, true);
            }

        }
        if ($this->playlistType === "mixer" && $this->playbackType !== "around_the_clock") {
            $playback_limit_type = $request->input('data.playback_limit.type', 'by_number');
            if (!in_array($playback_limit_type, ['by_number', 'by_minutes', 'by_date', 'by_time'])) {
               $playback_limit_type = 'by_number';
            }
            $playback_limit_value = $request->input('data.playback_limit.value');
            switch ($playback_limit_type) {
               case 'by_number':
               case 'by_minutes':
               case 'by_date':
                    $playback_limit_value = (int)$playback_limit_value;
                    if ($playback_limit_value <= 0) {
                        $this->valid = false;
                        $this->errors['playback_limit_value'] = ['scheduler.errors.enter_correct_number'];
                    }
                    break;
               case 'by_time':
                    if (!preg_match($time_regex, $playback_limit_value)) {
                        $this->valid = false;
                        $this->errors['playback_limit_value'] = ["scheduler.errors.enter_correct_time"];
                    } else {
                        $playback_limit_value_data = explode(":", $playback_limit_value);
                        $playback_limit_value = $playback_limit_value_data[0] * 60 + $playback_limit_value_data[1] + $timezone_offset;
                        if ($playback_limit_value < 0) {
                            $playback_limit_value = 3600 - $playback_limit_value;
                        }
                    }
                    break;
               default:
                   break;
            }
            if ($this->valid) {
               $this->playbackLimitType = $playback_limit_type;
               $this->playbackLimitValue = $playback_limit_value;
               if ($this->playbackLimitType === "by_time") {
                   $this->playbackLimitValueMinutes = $this->playbackLimitValue;
               }
            }
        }
    }

    public function getTotalDuration() {
        $duration = 0;
        foreach ($this->getItemsWithFolders() as $item) {
            $duration += $item->length;
        }
        return $duration;
    }
    public function isOnline() {
        return (bool)$this->getCurrentItem();
    }

    public function getCurrentItem() {
        if (count($this->getItemsWithFolders()) > 0) {
            $time = $this->time_start;
            if ($this->cycled && $this->cycledTill > time() && $this->time_start < time()) {
                $playlist_duration = $this->getTotalDuration();
                if ($playlist_duration > 0) {
                    $total_times = floor((time() - $this->time_start) / $playlist_duration);
                    $time = $this->time_start + $total_times * $playlist_duration;
                }
            }
            foreach ($this->getItemsWithFolders() as $item) {
                $length = $item->length;
                if ($time <= time() && time() <= $time + $length) {
                    return $item;
                }
                $time += $length;
            }
        }
        return null;
    }

    public function getNext($current_time, $min_length = null) {
        return $this->getForTimeInterval($current_time, $current_time + 1800, $min_length);
        $items = [];
        if (!$current_time) {
            $current_time = time();
        }
        if (count($this->getItemsWithFolders()) > 0) {
            $time = $this->time_start;
            if ($this->cycled && $this->cycledTill > $current_time && $this->time_start < $current_time) {
                $playlist_duration = $this->getTotalDuration();
                if ($playlist_duration > 0) {
                    $total_times = floor(($current_time - $this->time_start) / $playlist_duration);
                    $time = $this->time_start + $total_times * $playlist_duration;
                }
            }
            $found_item = false;
            //while (count($items) < $max_items) {
                foreach ($this->getItemsWithFolders() as $item) {
                    $length = $item->length;
                     //time()
                    if ($time <= $current_time && $current_time <= $time + $length) {
                        $found_item = true;
                    }

                    if ($found_item) {
                        if ($length >= $min_length) {
                            $item->setTime($time);
                            $items[] = $item;
                        }
                    }
                    $time += $length;
                }
           //` }
            return $items;
        }
        return [];
    }

    public function getForTimeInterval($time_start, $time_end, $min_length = 0) {

        if (count($this->getItemsWithFolders()) > 0) {
            if ($this->playbackType !== "around_the_clock") {
                if ($this->playlistType == "default") {
                    $items = [];
                    $times = [];
                    if ($this->playbackType === "default") {
                        $times = [$this->time_start];
                    } else {
                        //$offset = (int)request()->header('X-Timezone-Offset');
                        $offset = 0;
                        $times = [];
                        $time = $time_start;
                        $days = (int)ceil(($time_end - $time_start) / 86400);
                        for ($i = 0; $i < $days; $i++) {
                            $week_day = (int)date('w', $time) + 1;
                            if (isset($this->playbackDays->{$week_day}) && $this->playbackDays->{$week_day}) {
                                $times[] = $time  + $this->playbackTimeStart * 60 - ($offset * 60);
                            }
                            $time += 86400;
                        }
                    }

                    foreach ($times as $time) {
                        $times_to_repeat = 1;
                        if ($this->cycled && $this->cycledTill > $time_start && $this->time_start < $time_start) {
                            $playlist_duration = $this->getTotalDuration();
                            if ($playlist_duration > 0) {
                                $total_times = floor(($time_start - $this->time_start) / $playlist_duration);
                                $time = $this->time_start + $total_times * $playlist_duration;
                            }
                            $times_to_repeat = ceil(($time_end - $time_start) / $playlist_duration) + 1;
                        }
                        $is_valid = true;
                        foreach ($this->getItemsWithFolders() as $item) {
                            if ($item->length < $min_length) {
                                //$is_valid = false;
                            }
                        }
                        if ($is_valid) {
                            for ($i = 0; $i < $times_to_repeat; $i++) {
                                foreach ($this->getItemsWithFolders() as $item) {
                                    $length = $item->length;
                                    if ($length > $min_length) {
                                        if ($time <= $time_end && $time >= $time_start) {
                                            $cloned = clone $item;
                                            $cloned->setTime($time);
                                            $items[] = $cloned;
                                        }
                                    }
                                    $time += $length;
                                }
                            }
                        }

                        return $items;
                    }

                } else {
                    if($this->playbackType === "default") {
                        if ($this->time_start >= $time_start && $this->time_start <= $time_end) {
                            return [$this->toTimetableView()];
                        }
                    } else {
                        if ($this->playbackType === "repeating") {
                            $offset = 0;
                            $items = [];
                            $time = $time_start;
                            $days = (int)ceil(($time_end - $time_start) / 86400);
                            for ($i = 0; $i < $days; $i++) {
                                $week_day = (int)date('w', $time) + 1;
                                if (isset($this->playbackDays->{$week_day}) && $this->playbackDays->{$week_day}) {
                                    $start = $time  + $this->playbackTimeStart * 60 - ($offset * 60);
                                    $items[] = $this->toTimetableView($start);
                                }
                                $time += 86400;
                            }
                            return $items;
                        }
                    }
                }
            }
        }
        return [];
    }

    public function toTimetableView($start = null) {
        if (!$start) {
            $start = $this->time_start;
        }
        return (object)[
                'channel_id' => $this->channel_id,
                'time' => $start,
                'type' => 'autopilot_playlist',
                'id' => $this->playlist_id,
                'title' => $this->title,
        ];
    }

    public function getItemTime($id, $day = null) {
        $id = (int)$id;
        $time = null;
        $start_time = null;
        if ($this->playbackType === "default") {
            $time = $this->time_start;
        } else {
            if ($this->playbackType === "repeating") {
                $day_index = date('w', strtotime($day. "00:00")) + 1;
                 if (isset($this->playbackDays->{$day_index}) && $this->playbackDays->{$day_index}) {
                    //$offset = (int)request()->header('X-Timezone-Offset');
                    $offset = 0;
                    $time = strtotime($day. "00:00") + $this->playbackTimeStart * 60 - ($offset * 60);

                }
            }
        }
        if (!$time) {
            return null;
        }
        foreach($this->getItemsWithFolders() as $item) {
            if ($item->id === $id) {
               $start_time = $time;
            }
            $time += $item->length;
        }
        return $start_time;
    }

    public function getPlaylistTime($day = null) {
        if ($this->playbackType === "default") {
            return $this->time_start;
        } elseif ($this->playbackType === "repeating") {
            $date = strtotime($day. "00:00");
            return $date + $this->playbackTimeStart * 60;
        } else {
            return null;
        }
    }

    public function jsonSerialize() {

        $data =  [
            'time_start' => $this->time_start,
            'title' => $this->title,
            'cycled' => [
                'status' => $this->cycled,
                'till' => $this->cycledTill
            ],
            'type' => $this->playlistType,
            'playback_type' => $this->playbackType,
            'color' => $this->color
        ];
        //$timezone_offset = (int)request()->header('x-timezone-offset');
        $timezone_offset = 0;
        if (!$timezone_offset) {
            $timezone_offset = 0;
        }
        if ($this->playbackTimeStart) {
            $data['playback_time_start_minutes'] = $this->playbackTimeStart;
            $data['playback_time_start'] = date('H:i', mktime(0, $this->playbackTimeStart - $timezone_offset));
        }
        if ($this->playbackDays && (is_array($this->playbackDays) || is_object($this->playbackDays))) {
            $data['playback_days'] = $this->playbackDays;
        }
        if ($this->playbackLimitType) {
            $data['playback_limit'] = [
                'type' => $this->playbackLimitType,
                'value' => $this->playbackLimitValue
            ];
            if ($this->playbackLimitType === "by_time") {
                $data['playback_limit']['value_minutes'] = $this->playbackLimitValueMinutes;
                $data['playback_limit']['value'] = date('H:i', mktime(0, $this->playbackLimitValueMinutes - $timezone_offset));
            }
        }
        if ($this->playlist_id) {
            $data['id'] = $this->playlist_id;
        }
        return $data;
    }

    public function getPlaylistId() {
        return $this->playlist_id;
    }

    public function canReplay() {
        return false;
    }

    public function findItemById($id) {
        foreach ($this->getItemsWithFolders() as $item) {
            if ($id == $item->video_id) {
                return $item;
            }
        }
        return null;
    }
}
