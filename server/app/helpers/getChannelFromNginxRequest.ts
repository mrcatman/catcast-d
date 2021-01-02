import {Channel} from "../models/Channel";
import {StreamKey} from "../models/StreamKey";
import {User} from "../models/User";
import {FastifyRequest} from "fastify";

export default async function getChannelFromNginxRequest(req): Promise<[Channel, User]> {
    let key = req.body.key;
    let channelId = parseInt(req.body.name);
    let keyInstance = await StreamKey.findOne({key}, {relations: ['channel', 'user']});
    if (keyInstance && keyInstance.channel && keyInstance.channel.id === channelId) {
        return [keyInstance.channel, keyInstance.user];
    }
    throw new Error();
}
