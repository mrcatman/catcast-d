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
            where: { user: { id: req.user.id } },
            relations: ['channel']
        });
        res.send({ permissions });
    })

    fastify.post('/:id/confirm', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let permissions = await UserPermissions.findOne({
            where: { id: req.params.id, user: {id: req.user.id} },
            relations: ['user']
        });
        if (!permissions) {
            throw {
                status: 403, error: 'common.access_error'
            };
        }
        permissions.confirmed = true;
        await permissions.save();
        res.send({permissions});
    })

    fastify.post('/:id/reject', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let permissions = await UserPermissions.findOne({
            where: { id: req.params.id, user: {id: req.user.id} },
            relations: ['user']
        });
        if (!permissions) {
            throw {
                status: 403, error: 'common.access_error'
            };
        }
        permissions.rejected = true;
        await permissions.save();
        res.send({permissions});
    })

}

module.exports = routes;
