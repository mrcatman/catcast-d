import {User} from "../../models/User";
import {Channel} from "../../models/Channel";
import { UserChannelPermissions } from './list'

type ChannelPermissionValue = keyof typeof UserChannelPermissions;

export async function getPermissions(user: User, channel: Channel) {
    if (channel.owner && channel.owner.id === user.id) {
        return Object.values(UserChannelPermissions);
    } else {
        return [];
    }
}

export async function checkPermissions(user: User, channel: Channel, permissions: Array<ChannelPermissionValue>, needFullAccess: boolean = false) {
    //await channel.owner;
    // todo: make a permission system
    return channel.owner.id === user.id;
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



