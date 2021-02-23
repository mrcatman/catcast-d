import {ServerInstance} from "../types";
import { config, setConfig } from '../config'
import { Channel } from '../models/Channel'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/config', {
        preValidation: [fastify.authenticate_admin]
    }, async (req, res) => {
        res.send({
            config: config()
        });
    })

    fastify.post('/config', {
        preValidation: [fastify.authenticate_admin]
    }, async (req, res) => {
        await setConfig(req.body);
        res.send({
            status: true
        });
    })

    fastify.get('/frontend-config',  async (req, res): Promise<any> => {
        let configValues = config('frontend');
        configValues.domain = config('server.domain');
        res.send({
            config: configValues
        });
    })

}

module.exports = routes;
