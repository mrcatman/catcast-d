<?php

namespace App\Helpers;


use function Symfony\Component\String\b;

class ConfigHelper {

    public static function siteURL() {
        return config('urls.app_url');
    }

    public static function streamsURL() {
        return config('urls.broadcast.hls_url');
    }

    public static function rtmpURL() {
        return config('urls.broadcast.rtmp_url');
    }

    public static function rtmpAppName() {
        return config('urls.broadcast.rtmp_app_name');
    }

    public static function maxChannelsCount($type) {
        return (int)config('site.users.quotas.count.'.$type, 1);
    }

    public static function diskSpace($type) {
        return (int)config('site.users.quotas.disk.'.$type, 0);
    }


    public static function channelTypeAllowed($type) {
        return (bool)config('site.users.allowed_channel_types.'.$type, true);
    }

    public static function registrationManual() {
        return (bool)config('site.users.registration_manual', false);
    }

    public static function enableHLS() {
        return (bool)config('site.media.video.enable_hls', true);
    }

    public static function statisticsSessionDurationSeconds() {
        return (int)config('site.statistics.session_duration_seconds', 300);
    }

    public static function statisticsStoreCountries() {
        return (bool)config('site.statistics.store_countries', true);
    }

    public static function statisticsGeoIPDatabase() {
        return config('site.statistics.geoip_database', 'GeoLite2-Country.mmdb');
    }

    public static function commentsMaxChildrenToLoad() {
        return config('site.comments.max_children_to_load', 5);
    }

    public static function enableDislikes($entity_type) {
        return (bool)config('site.rating.enable_dislikes.'.$entity_type, false);
    }

    public static function showLikedUsers($entity_type) {
        return (bool)config('site.rating.show_users.'.$entity_type, true);
    }

}
