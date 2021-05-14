import {ServerInstance} from "../types";
import { config, setConfig } from '../config'
import { Channel } from '../models/Channel'
import { User } from '../models/User'
import { IsNull, Like, Not } from 'typeorm'
import { Role } from '../helpers/roles'

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

    fastify.get('/users', {
        preValidation: [fastify.authenticate_moderator]
    },  async (req, res): Promise<any> => {
        let where: any = [];
        let domain = null;
        if (req.query.type === 'remote') {
            domain = Not(IsNull());
        }
        if (req.query.type === 'local') {
            domain = IsNull();
        }
        if (req.query.search) {
            where = [
                {login: Like(`%${req.query.search}%`)},
                {email: Like(`%${req.query.search}%`)},
                {name: Like(`%${req.query.search}%`)}
            ]
            if (domain) {
                where.forEach(condition => {
                    condition.domain = domain;
                })
            }
        } else {
            where = {};
            if (domain) {
                where.domain = domain;
            }
        }

         let users = await User.paginate({
            order: {
                id: 'DESC',
            },
            where
        }, req);
        res.send({
            users
        });
    })

    fastify.post('/users/:id', {
        preValidation: [fastify.authenticate_moderator]
    },  async (req, res): Promise<any> => {
        let user = await User.findOneOrFail({id: req.params.id});
        if (req.body.is_blocked !== undefined) {
            let isBlocked = !!req.body.is_blocked;
            user.is_blocked = isBlocked;
        }
        const myRole = req.user.role_id;
        if (myRole >= Role.ADMIN) {
            if (req.body.role_id !== undefined) {
                user.role_id = parseInt(req.body.role_id);
            }
        }
        await user.save();
        res.send(user);
    })

    fastify.get('/channels', {
        preValidation: [fastify.authenticate_admin]
    },  async (req, res): Promise<any> => {
        let channels = await Channel.paginate({
            order: {
                id: 'DESC',
            },
            where: {}
        }, req);
        res.send({
            channels
        });
    })

}

module.exports = routes;
