import {User} from "../../models/User";
import {Channel} from "../../models/Channel";
import { UserChannelPermissions } from './list'
import { UserPermissions } from '../../models/UserPermissions'

type ChannelPermissionValue = keyof typeof UserChannelPermissions;

export async function getPermissions(user: User, channel: Channel): Promise<Array<any>> {
    if (channel.owner && channel.owner.id === user.id) {
        return [...Object.values(UserChannelPermissions), 'FULL_ADMIN'];
    } else {
        let permissions = await UserPermissions.findOne({
            where: {
                channel: {
                    id: channel.id
                },
                user: {
                    id: user.id
                }
            }
        })
        if (permissions) {
            return permissions.list;
        }
        return [];
    }
}

export async function checkPermissions(user: User, channel: Channel, permissions: Array<ChannelPermissionValue>, needFullAccess: boolean = false) {
    if (channel.owner.id === user.id) {
        return true;
    }
    let userPermissions = await getPermissions(user, channel);
    let hasPermission = false;
    permissions.forEach(permission => {
        if (userPermissions.indexOf(permission) !== -1) {
            hasPermission = true;
        } else {
            hasPermission = false;
        }
    })
    return hasPermission;
}

export async function checkPermissionsOrFail(user: User, channel: Channel, permissions: Array<ChannelPermissionValue>, needFullAccess: boolean = false) {
    let status = await checkPermissions(user, channel, permissions, needFullAccess);
    if (!status) {
        throw {
            status: 401,
            error: "global.errors.access_error"
        };
    } else {
        return true;
    }
}



