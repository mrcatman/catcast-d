import {ServerInstance} from "../types";
import { config } from '../config'
import { Channel } from '../models/Channel'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/config', {
        preValidation: [fastify.authenticate_admin]
    }, async (req, res) => {
        res.send({
            config: config()
        });
    })

    fastify.get('/frontend-config',  async (req, res): Promise<any> => {
        res.send({
            config: config('frontend')
        });
    })

}

module.exports = routes;
