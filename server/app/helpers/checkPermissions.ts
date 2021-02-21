import {User} from "../models/User";
import {Channel} from "../models/Channel";
import { ChannelPermissions } from './channelPermissions'

type ChannelPermissionValue = keyof typeof ChannelPermissions;

export async function getPermissions(user: User, channel: Channel) {
    if (channel.owner && channel.owner.id === user.id) {
        return Object.values(ChannelPermissions);
    } else {
        return [];
    }
}

export async function checkPermissions(user: User, channel: Channel, permissions: Array<ChannelPermissionValue>) {
    //await channel.owner;
    // todo: make a permission system
    return channel.owner.id === user.id;
}

export async function checkPermissionsOrFail(user: User, channel: Channel, permissions: Array<ChannelPermissionValue>) {
    let status = await checkPermissions(user, channel, permissions);
    if (!status) {
        throw {
            status: 401,
            error: "global.errors.access_error"
        };
    } else {
        return true;
    }
}



