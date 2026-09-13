<?php

namespace App\Helpers;

use App\Models\Channel;
use App\Models\User;
use App\Models\UserChannelPermissions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;

class PermissionsHelper {


    public static function getAvailableTypeIds() {
        return collect(UserChannelPermissions::LIST)->map(function($item) {
            return $item['id'];
        });
    }

    /**
     * Get associative array containing current user's permissions
     * @param Channel $channel Channel for which the permissions are checked
     * @param User|null $user |null User for which the permissions are checked
     * @param bool $check_admin Whether to also check if user is an administrator
     * @return array[]
     */
    public static function getForChannel(Channel $channel, User $user = null, bool $check_admin = true) {
        if (!$user) {
            $user = auth()->user();
        }
        if (!$user) {
            return [];
        }
        if ($user->is_admin && $check_admin) {
            return [
                'admin' => UserChannelPermissions::PERMISSIONS_STANDARD
            ];
        }
        if ($channel->user_id == $user->id) {
            return [
                'owner' => UserChannelPermissions::PERMISSIONS_STANDARD
            ];
        }

        $permissions_instance = UserChannelPermissions::where('user_id', $user->id)->where('channel_id',$channel->id)->first();
        if ($permissions_instance) {
            return $permissions_instance->permissions;
        }
        return [];
    }

    /**
     * Get if the user is in channel's team or is an administrator
     * @param Channel $channel Channel for which the permissions are checked
     * @param User|null $user User for which the permissions are checked
     * @return boolean
     */
    public static function hasAny(Channel $channel, User $user = null) {
        $permissions = self::getForChannel($channel, $user);
        return array_keys($permissions) > 0;
    }

    /**
     * Return channel if user has given permissions, otherwise throw exception
     * @param int $channel_id Channel ID
     * @param string[] $allowed_permissions Permissions which need to be checked
     * @throws AuthorizationException
     * @return Channel
     */
    public static function getChannelIfAllowed(int $channel_id, array $allowed_permissions = []) {
        $channel = Channel::findOrFail($channel_id);
        self::check($allowed_permissions, $channel);
        return $channel;
    }

    /**
     * Return channel if user has given permissions, otherwise throw exception
     * @param string[] $allowed_permissions Permissions which need to be checked
     * @param Channel $channel Channel for which the permissions are checked
     * @param Model $entity Entity for
     * @return boolean
     */
    public static function getStatus(array $allowed_permissions, Channel $channel, $entity = null, $full = false, $user = null) {
        if (!$user) {
            $user = auth()->user();
        }
        if (!$user) {
            return false;
        }

        $has_entity = $entity && $entity->exists;
        $permissions = self::getForChannel($channel);
        $allowed_permissions = array_merge($allowed_permissions, ['owner', 'channel_admin', 'admin']);
        foreach ($permissions as $key => $val) {
            if ($full && in_array($key, $allowed_permissions) && $val == UserChannelPermissions::PERMISSIONS_FULL) {
                return true;
            }
            if ($has_entity) {
                $entity_class = get_class($entity);
                $user_id = defined($entity_class.'::USER_ID_KEY') ? $entity[$entity_class::USER_ID_KEY] : $entity->user_id;

                if ($user->id == $user_id) {
                    return true;
                }
            }
            if ((!$has_entity && !$full) && in_array($key, $allowed_permissions) && $val == UserChannelPermissions::PERMISSIONS_STANDARD)  {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the user is in channel's team or is an administrator, throw an exception if not
     * @param string[] $allowed_permissions Permissions which need to be checked
     * @param Channel|null $channel Channel for which the permissions are checked
     * @param Model|null $entity Channel for which the permissions are checked
     * @throws AuthorizationException
     * @void
     */
    public static function check($allowed_permissions = [], Channel $channel = null, Model $entity = null) {
        $status = self::getStatus($allowed_permissions, $channel, $entity);
        if (!$status) {
            throw new AuthorizationException();
        }
    }

    /**
     * Check if the user is in channel's team or is an administrator, throw an exception if not
     * @param Channel $channel Channel for which the permissions are checked
     * @throws AuthorizationException
     * @void
     */
    public static function checkHasAny(Channel $channel) {
        $status = PermissionsHelper::hasAny($channel);
        if (!$status) {
            throw new AuthorizationException();
        }
    }

}
