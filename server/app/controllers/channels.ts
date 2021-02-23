import { remoteSearch } from '../federation/remoteSearch'

import { Channel } from '../models/Channel';
import {ServerInstance} from "../types";
import { checkPermissionsOrFail, getPermissions } from '../helpers/checkPermissions'
import { getBaseChannelValidators, getFollowConditions } from '../helpers/channels'
import {generateKeys} from "../federation/crypto";
import { Follower } from '../models/Follower'
import {Like} from "typeorm";
import { Follow, Unfollow } from '../federation/activities/Follow'
import { Update, UpdateActor } from '../federation/activities/Create'
import { ChannelPermissions } from '../helpers/channelPermissions'
import { Stream } from '../models/Stream'

const NotEmptyValidator = require('../validation/validators/NotEmptyValidator');
const MaxLengthValidator = require('../validation/validators/MaxLengthValidator');

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
        let channels = await Channel.paginate({owner: {
            id: req.user.id
        }}, req);
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
        await checkPermissionsOrFail(req.user, channel, [ChannelPermissions.EDIT_STREAM_INFO]);
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
            relations: ['current_stream']
        });
        res.send({channel});
    });

    fastify.get('/get-by-url/:url', async (req, res) => {
        let channel = await Channel.findOneOrFail({url: req.params.url}, {
            relations: ['current_stream']
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
            subscription.fill(getFollowConditions(req.user, channel));
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

        let subscription = await Follower.findOne(getFollowConditions(req.user, channel))
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
        let is_subscribed = req.user ? !!(await Follower.findOne(getFollowConditions(req.user, channel))) : false;
        res.send({
            subscribers_count,
            is_subscribed
        });
    })

    fastify.put('/:id/stream-settings', {
        preValidation: [fastify.authenticate]
    }, async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id}, {relations: ['owner', 'current_stream']});
        await checkPermissionsOrFail(req.user, channel, [ChannelPermissions.EDIT_STREAM_INFO]);
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

}

module.exports = routes;
