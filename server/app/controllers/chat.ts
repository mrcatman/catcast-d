import {ServerInstance} from "../types";
import { Channel } from '../models/Channel'
import { randomBytes } from 'crypto';
import { Connect } from '../federation/activities/Chat'
import { keysStorage } from '../helpers/chat/keysStorage'
import { chatManager } from '../helpers/chat/manager'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/:id/connect', {
        preValidation: [fastify.authenticate],
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id});

        if (channel.domain) {
            const connectKey = randomBytes(24).toString('hex');
            await Connect(connectKey, req.user, channel);
            res.send({
                status: 1,
                data: {
                    connect_key: connectKey
                }
            });
        } else {
            res.send({
                status: 0,
            });
        }
    })

    fastify.get('/:url/realtime', {
        preValidation: [fastify.authenticate_optional],
        websocket: true
    }, async (connection, req) => {
        let channel = await Channel.findOneOrFail({url: req.params.url}, {relations: ['owner']});
        let user = req.user;
        if (!user) {
            user = keysStorage.get(req.query.connect_key);
        }
        await chatManager.addUser(channel, user, connection.socket);
    })

}

module.exports = routes;
