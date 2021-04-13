import { remoteSearch } from '../federation/remoteSearch'

import { Channel } from '../models/Channel';
import {ServerInstance} from "../types";

import { UserPermissions } from '../models/UserPermissions'
import { checkPermissionsOrFail, getPermissions } from '../helpers/permissions/check'
import { RemoteAvailableChannelPermissions, UserChannelPermissions } from '../helpers/permissions/list'

import { getBaseChannelValidators } from '../helpers/channels'
import {getFollowConditionsChannel} from '../helpers/follow'

import {generateKeys} from "../federation/crypto";
import { Follower } from '../models/Follower'
import {Like, In} from "typeorm";
import { Follow, Unfollow } from '../federation/activities/Follow'
import { Update, UpdateActor } from '../federation/activities/Create'

import { Stream } from '../models/Stream'

import { config } from '../config'
import { User } from '../models/User'
import { getActorByHandle } from '../federation/remoteActor'
import { CancelOffer, Offer } from '../federation/activities/Team'

const NotEmptyValidator = require('../validation/validators/NotEmptyValidator');
const MaxLengthValidator = require('../validation/validators/MaxLengthValidator');
const ArrayValidator = require('../validation/validators/ArrayValidator');
const BooleanValidator = require('../validation/validators/BooleanValidator');

async function routes (fastify: ServerInstance, options) {

    fastify.get('/', async (req, res) => {
        let channels = await Channel.paginate({
            relations: ['current_stream']
        }, req);
        res.send(channels);
    })

    fastify.get('/local', async (req, res) => {
        let channels = await Channel.paginate({
            where: {
                domain: null
            },
            relations: ['current_stream']
        }, req);
        res.send(channels);
    })

    fastify.get('/online', async (req, res) => {
        let channels = await Channel.paginate({
            where: {
                is_online: true
            },
            relations: ['current_stream']
        }, req);
        res.send(channels);
    })

    fastify.get('/my', {
        preValidation: [fastify.authenticate]
    }, async (req, res) => {
        let channelIdsWithPermissions = (await UserPermissions.find({
            where: { user: { id: req.user.id} },
            relations: ['channel']
        })).map(permission => permission.channel.id);
        let channels = await Channel.paginate({
            where: [
                {id: In(channelIdsWithPermissions)},
                {domain: null},
                {owner: { id: req.user.id }}
            ]
        }, req);
        res.send(channels);
    })

    fastify.get('/search', async (req, res) => {
        let query = req.query.q || "";
        let remoteChannel;
        try {
            remoteChannel = await remoteSearch(query);
        } catch (e) {

        }
        let channels = await Channel.paginate({
            where: [
               {name: Like(`%${query}%`)},
                {url: Like(`%${query}%`)}
            ]
        }, req);
        if (remoteChannel) {
            channels.list.unshift(remoteChannel);
        }
        res.send(channels);
    })

    fastify.post('/', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let data = await fastify.validate(req, getBaseChannelValidators());
        let channel = new Channel();
        channel.fill(data);
        channel.owner = req.user;
        let {publicKey, privateKey} = await generateKeys();
        channel.public_key = publicKey;
        channel.private_key = privateKey;

        await channel.save();

        channel.private_key = undefined;

        res.send({
            channel
        });
    })

    fastify.put('/:id', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner']});
        await checkPermissionsOrFail(req.user, channel, [UserChannelPermissions.EDIT_STREAM_INFO]);
        let data = await fastify.validate(req, getBaseChannelValidators());
        channel.fill(data);
        await channel.save();
        UpdateActor(channel);
        res.send({
            channel
        });
    })

    fastify.get('/:id', async (req, res) => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {
            relations: ['current_stream', 'owner']
        });
        res.send({channel});
    });

    fastify.get('/get-by-url/:url', async (req, res) => {
        let channel = await Channel.findOneOrFail({url: req.params.url}, {
            relations: ['current_stream', 'owner']
        });
        res.send({channel});
    });

    fastify.get('/:id/permissions', {
        preValidation: [fastify.authenticate_optional]
    }, async (req, res) => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner']});
        let permissions = [];
        if (req.user) {
            permissions = await getPermissions(req.user, channel);
        }
        res.send({permissions});
    });


    fastify.post('/:id/subscribe', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id});

        let subscription = await Follower.findOne({
            follower: {
                id: req.user.id
            },
            actor_id: channel.id,
            actor_type: Follower.TYPE_CHANNEL
        })
        if (!subscription) {
            subscription = new Follower();
            subscription.fill(getFollowConditionsChannel(req.user, channel));
            await subscription.save();
        }
        if (channel.domain) {
            Follow(req.user, channel);
        }
        res.send({
            status: true
        });
    })

    fastify.post('/:id/unsubscribe', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id});

        let subscription = await Follower.findOne(getFollowConditionsChannel(req.user, channel))
        if (subscription) {
            await subscription.remove();
        }
        if (channel.domain) {
            Unfollow(req.user, channel);
        }
        res.send({
            status: true
        });
    })

    fastify.get('/:id/subscribers-count', {
        preValidation: [fastify.authenticate_optional]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id});

        let subscribers_count = (await Follower.find({
            actor_id: channel.id,
            actor_type: Follower.TYPE_CHANNEL
        })).length;
        if (channel.followers_count) {
            subscribers_count+= channel.followers_count;
        }
        let is_subscribed = req.user ? !!(await Follower.findOne(getFollowConditionsChannel(req.user, channel))) : false;
        res.send({
            subscribers_count,
            is_subscribed
        });
    })

    fastify.put('/:id/stream-settings', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner', 'current_stream']});
        await checkPermissionsOrFail(req.user, channel, [UserChannelPermissions.EDIT_STREAM_INFO]);
        let data = await fastify.validate(req, {
            name: [new NotEmptyValidator()],
            description: [new MaxLengthValidator(500)],
        });
        channel.stream_settings_value = JSON.stringify(data);
        await channel.save();
        if (channel.current_stream) {
            channel.current_stream.fill(data);
            await channel.current_stream.save();
            Update(channel.current_stream);
        }
        res.send({
            status: 1,
        });
    })

    fastify.get('/:id/streams', async (req, res): Promise<any> => {
        let streams = await Stream.paginate({
            where: {
                channel_id: parseInt(req.params.id)
            },
            order: {
                started_at: 'DESC',
            },
        }, req)
        res.send({
            streams
        });
    })

    fastify.get('/:id/team', async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner']});
        //await checkPermissionsOrFail(req.user, channel, [], true);
        let team = (await UserPermissions.find({
            where: {
                channel: {
                    id: req.params.id
                }
            },
            relations: ['user']
        })).filter(permission => permission.user);
        let owner = channel.owner;
        res.send({
            team,
            owner
        });
    })

    fastify.post('/:id/team', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner']});
        await checkPermissionsOrFail(req.user, channel, [], true);
        let data = await fastify.validate(req, {
            user: [new NotEmptyValidator()],
            full: [new BooleanValidator()],
            permissions: [new ArrayValidator()],
            comment: [new MaxLengthValidator(255)],
        });
        if (!data.full && data.permissions.length === 0) {
            throw {
                status: 422,
                errors: {
                    user: ['dashboard.team._errors.select_rights']
                }
            };
        }
        let user;
        let handleInfo = data.user.split('@');
        if (handleInfo.length === 1 || handleInfo[1] === config('server.domain')) {
            user = await User.findOne({
                where: {
                    login: handleInfo[0],
                    domain: null
                }
            })
            if (!user) {
                throw {
                    status: 422, errors: { user: ['dashboard.team._errors.user_not_found_local'] }
                };
            }
        } else {
            try {
                user = await getActorByHandle(data.user);
            } catch (e) {

            }
            if (!user || user.toObject().catcastActorType === 'Channel') {
                throw {
                    status: 422, errors: { user: ['dashboard.team._errors.user_not_found_remote'] }
                };
            }
        }
        if (user && user.id === channel.owner.id) {
            throw {
                status: 422, errors: { user: ['dashboard.team._errors.user_is_owner'] }
            };
        }
        let permissions = await UserPermissions.findOne({
            where: {
                channel: {
                    id: channel.id
                },
                user: {
                    id: user.id
                }
            }
        });
        if (!permissions) {
            permissions = new UserPermissions();
            permissions.user = user;
            permissions.channel = channel;
        }
        let permissionsList = data.permissions.filter(permission => Object.keys(UserChannelPermissions).indexOf(permission) !== -1);
        if (user.domain) {
            permissionsList = permissionsList.filter(permission => RemoteAvailableChannelPermissions.indexOf(permission) !== -1);
        }
        permissions.fill({
            full: !user.domain ? data.full : false,
            list_string: JSON.stringify(permissionsList),
            comment: data.comment
        })
        await permissions.save();
        Offer(permissions);
        return permissions;
    })

    fastify.delete('/:id/team/:permissions_id', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner']});
        await checkPermissionsOrFail(req.user, channel, [], true);
        let permissions = await UserPermissions.findOneOrFail({
            id: req.params.permissions_id
        }, {
            relations: ['channel', 'user']
        });
        if (permissions.channel.id !== channel.id) {
            throw {
                status: 403, error: 'common.access_error'
            };
        }
        await permissions.remove();
        CancelOffer(permissions);
        return true;
    })

    fastify.get('/:id/team/public', async (req, res): Promise<any> => {
        let team = (await UserPermissions.find({
            where: {
                channel: {
                    id: req.params.id
                },
                confirmed: true,
                hidden: false
            },
            relations: ['user']
        })).filter(permission => permission.user).map(permission => {
            return {
                user: permission.user,
                comment: permission.comment
            }
        });
        res.send({
            team,
        });
    })

}

module.exports = routes;
