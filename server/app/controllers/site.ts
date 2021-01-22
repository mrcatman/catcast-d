import {ServerInstance} from "../types";
import { config } from '../config'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/info',  async (req, res): Promise<any> => {
        res.send({
            info: config('frontend')
        });
    })

}

module.exports = routes;
