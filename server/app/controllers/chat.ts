import {ServerInstance} from "../types";
import { Channel } from '../models/Channel'
import { randomBytes } from 'crypto';
import { Connect } from '../federation/activities/Chat'
import { keysStorage } from '../helpers/chat/keysStorage'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/:id', {
        preValidation: [fastify.authenticate],
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id});

        let data: any = {
            messages: []  // todo: messages list
        };

        if (channel.domain) {
            const connectKey = randomBytes(24).toString('hex');
            data.connect_key = connectKey;
            await Connect(connectKey, req.user, channel);
        }
        res.send({
            status: 1,
            data
        });
    })

    fastify.get('/:id/realtime', {
        preValidation: [fastify.authenticate_optional],
        websocket: true
    }, (connection, req) => {
        let user = req.user;
        if (!user) {
            user = keysStorage.get(req.query.connect_key);
        }
        if (user) {
            connection.socket.send(`CONNECTED`);
        } else {
            connection.close();
        }

    })

}

module.exports = routes;
