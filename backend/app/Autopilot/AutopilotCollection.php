<?php

namespace App\Autopilot;

class AutopilotCollection implements \JsonSerializable {

    protected $channel_id;
    protected $playlists = [];

    public function __construct($playlist_data, $channel_id) {
        $this->channel_id = $channel_id;
        if (is_iterable($playlist_data)) {
            foreach ($playlist_data as $playlist) {
                $this->playlists[] = $playlist;
            }
        }
    }

    public function getChannelId() {
        return $this->channel_id;
    }

    public function isOnline() {
        $is_online = false;
        if (count($this->playlists) > 0) {
            foreach ($this->playlists as $playlist) {
                if ($playlist->extended_data->isOnline()) {
                    $is_online = true;
                }
            }
        }
        return $is_online;
    }

    public function getCurrentItem() {
        if (count($this->playlists) > 0) {
            foreach ($this->playlists as $playlist) {
                if ($item = $playlist->extended_data->getCurrentItem()) {
                    return $item;
                }
            }
        }
        return null;
    }

    public function getNext($time = null) {
        if (!$time) {
            $time = time();
        }
        if (count($this->playlists) > 0) {
            $items = [];
            foreach ($this->playlists as $playlist) {
               $items = array_merge($items, $playlist->extended_data->getNext($time));
            }

            return $items;
        }
        return [];
    }

    public function getForTimeInterval($time_start = null, $time_end = null) {
        if (!$time_start) {
            $time_start = time();
        }
        if (!$time_end) {
            $time_end = time() + 86400;
        }
        if (count($this->playlists) > 0) {
            $items = [];
            foreach ($this->playlists as $playlist) {
                $items = array_merge($items, $playlist->extended_data->getForTimeInterval($time_start, $time_end));
            }
            return $items;
        }
        return [];
    }

    public function jsonSerialize() {
        return $this->getPlaylists();
    }

    public function getPlaylists() {

        return $this->playlists;
    }
}
