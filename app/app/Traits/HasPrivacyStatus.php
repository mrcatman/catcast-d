<?php

namespace App\Traits;

use App\Enums\PrivacyStatuses;

trait HasPrivacyStatus {

    public function scopeVisible($query) {
        return $query->where(['privacy_status' => PrivacyStatuses::PUBLIC]);
    }

    public function getPrivacyStatusNameAttribute() {
        $privacy_status_map = [
            PrivacyStatuses::PRIVATE => 'private',
            PrivacyStatuses::UNLISTED => 'unlisted',
            PrivacyStatuses::PUBLIC => 'public',
        ];
        return $privacy_status_map[$this->privacy_status] ?? null;
    }

}
