import {ServerInstance} from "../types";
import { RemoteAvailableChannelPermissions, UserChannelPermissions } from '../helpers/permissions/list'
import { UserPermissions } from '../models/UserPermissions'
import { Accept, Reject } from '../federation/activities/Team'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/', async (req, res) => {
        let permissions = Object.keys(UserChannelPermissions);
        let remote = RemoteAvailableChannelPermissions;
        res.send({ permissions, remote });
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
            relations: ['user', 'channel']
        });
        if (!permissions) {
            throw {
                status: 403, error: 'common.access_error'
            };
        }
        permissions.hidden = !!req.body.hidden;
        permissions.confirmed = true;
        await permissions.save();
        Accept(permissions);
        res.send({permissions});
    })

    fastify.post('/:id/reject', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let permissions = await UserPermissions.findOne({
            where: { id: req.params.id, user: {id: req.user.id} },
            relations: ['user', 'channel']
        });
        if (!permissions) {
            throw {
                status: 403, error: 'common.access_error'
            };
        }
        permissions.rejected = true;
        await permissions.save();
        Reject(permissions);
        res.send({permissions});
    })

}

module.exports = routes;
