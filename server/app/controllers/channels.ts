import { Channel } from '../models/Channel';
import {ServerInstance} from "../types";
import {checkRightsOrFail} from "../helpers/checkRights";
import {StreamKey} from "../models/StreamKey";
import { getBaseChannelValidators, getFollowConditions } from '../helpers/channels'
import {generateKeys} from "../federation/crypto";
import { Follower } from '../models/Follower'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/', async (req, res) => {
        let channels = await Channel.find();
        res.send({channels});
    })

    fastify.get('/my', {
        preValidation: [fastify.authenticate]
    }, async (req, res) => {
        let channels = await Channel.find({owner: {
            id: req.user.id
        }});
        res.send({channels});
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
        await checkRightsOrFail(req.user, channel, ['common']);
        let data = await fastify.validate(req, getBaseChannelValidators());
        channel.fill(data);
        await channel.save();
        res.send({
            channel
        });
    })

    fastify.get('/:id', async (req, res) => {
        let channel = await Channel.findOneOrFail({id: req.params.id});
        res.send({channel});
    });

    fastify.get('/get-by-url/:url', async (req, res) => {
        let channel = await Channel.findOneOrFail({url: req.params.url});
        res.send({channel});
    });

    fastify.get('/:id/rights', async (req, res) => {
        let rights = ['admin']; //TODO
        res.send({rights});
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
        res.send({
            status: true
        });
    })

    fastify.get('/:id/subscribers-count', async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({id: req.params.id});

        let subscribers_count = (await Follower.find({
            actor_id: channel.id,
            actor_type: Follower.TYPE_CHANNEL
        })).length
        let is_subscribed = req.user ? !!(await Follower.findOne(getFollowConditions(req.user, channel))) : false;
        res.send({
            subscribers_count,
            is_subscribed
        });
    })

}

module.exports = routes
