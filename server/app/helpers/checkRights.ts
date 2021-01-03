import {User} from "../models/User";
import {Channel} from "../models/Channel";

export async function checkRights(user: User, channel: Channel, roles: Array<string>) {
    //await channel.owner;
    return channel.owner.id === user.id;
}

export async function checkRightsOrFail(user: User, channel: Channel, roles: Array<string>) {
    let status = await checkRights(user, channel, roles);
    if (!status) {
        throw {
            status: 401,
            error: "global.errors.access_error"
        };
    } else {
        return true;
    }
}






