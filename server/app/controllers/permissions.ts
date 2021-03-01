import {ServerInstance} from "../types";
import { UserChannelPermissions } from '../helpers/permissions/list'
import { UserPermissions } from '../models/UserPermissions'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/', async (req, res) => {
        let permissions = Object.keys(UserChannelPermissions);
        res.send({ permissions });
    })

    fastify.get('/my', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let permissions = await UserPermissions.find({
            where: {
                user: {
                    id: req.user.id
                }
            },
            relations: ['channel']
        });

        res.send({ permissions });
    })

}

module.exports = routes;
