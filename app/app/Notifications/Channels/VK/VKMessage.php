<?php

namespace App\Notifications\Channels\VK;

use Illuminate\Support\Facades\Storage;

class Message {

    protected $content = "";
    protected $attachment_id = null;
    protected $user_id = null;

    public function __construct() {

    }

    public function getContent() {
        return $this->content;
    }

    public function getAttachmentId() {
        return $this->attachment_id;
    }

    public function content($content) {
        $this->content = $content;
        return $this;
    }

    public function userId($user_id) {
        $this->user_id = $user_id;
        return $this;
    }

    public function picture($picture) {
        $cache_filename = str_replace("https://", "", $picture);
        $cache_filename = str_replace("http://", "", $cache_filename);
        $cache_filename = str_replace("/", "_", $cache_filename);
        $exists = Storage::disk('pictures_cache')->exists($cache_filename);
        $attachment_id = null;
        $storage_path  = Storage::disk('pictures_cache')->getDriver()->getAdapter()->getPathPrefix();
        if ($exists) {
            if (Storage::disk('pictures_cache')->exists('attachments.json')) {
                $attachments_cache = Storage::disk('pictures_cache')->get('attachments.json');
                $attachments_cache = json_decode($attachments_cache, 1);
                if (isset($attachments_cache[$cache_filename])) {
                    $attachment_id = $attachments_cache[$cache_filename];
                }
            }
        } else {
            file_put_contents("$storage_path/$cache_filename", fopen($picture, 'r'));
        }
        if ($attachment_id) {
            $this->attachment_id = "photo".$attachment_id;
        } else {
            $server = $this->apiRequest("photos.getMessagesUploadServer", [
                'peer_id' => $this->user_id
            ]);
            $upload_url = $server->response->upload_url;
            $curl = curl_init($upload_url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new \CURLfile("$storage_path/$cache_filename")));
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);
            $save = $this->apiRequest("photos.saveMessagesPhoto", [
                'photo' => $response->photo,
                'server' => $response->server,
                'hash' => $response->hash
            ]);
            $attachment_id = $save->response[0]->owner_id."_".$save->response[0]->id;
            $this->attachment_id = "photo".$attachment_id;
            if (Storage::disk('pictures_cache')->exists('attachments.json')) {
                $attachments_cache = Storage::disk('pictures_cache')->get('attachments.json');
                $attachments_cache = json_decode($attachments_cache, 1);
            } else {
                $attachments_cache = [];
            }
            $attachments_cache[$cache_filename] = $attachment_id;
            Storage::disk('pictures_cache')->put('attachments.json', json_encode($attachments_cache));
        }
        return $this;
    }

    public function apiRequest($method, $params) {
        $request_params = $params;
        $request_params['access_token'] = config('services.vkontakte.token');
        $request_params['v'] = '5.87';
        $ch = curl_init("https://api.vk.com/method/$method");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request_params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response);
    }
}