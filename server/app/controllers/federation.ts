import {ServerInstance} from "../types";
import {User} from "../models/User";
import { ITEMS_ON_PAGE } from '../federation/constants'
import {Channel} from "../models/Channel";
import { Follower } from '../models/Follower'
import { Stream } from '../models/Stream'
import { context } from '../federation/context'
import { handleInbox } from '../federation/handleInbox'
import { config } from '../config'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/users/:login', async (req, res): Promise<any> => { // get actor for a user
        let user = await User.findOneOrFail({ login: req.params.login }, {
            relations: ['avatar']
        });
        res.header('Content-Type', 'application/activity+json').send({
            '@context': context,
            ...user.toObject()
        });
    });

    fastify.get('/channels/:url', async (req, res): Promise<any> => { // get actor for a channel
        let channel = await Channel.findOneOrFail({ url: req.params.url }, {
            relations: ['logo', 'owner']
        });
        res.header('Content-Type', 'application/activity+json').send({
            '@context': context,
            ...channel.toObject()
        });
    });

    async function getFollowCollection(req, conditions, following = false) {
        const relationsToLoad = !following ? ['follower'] : [];
        const followsCount = await Follower.count({
            where: conditions,
        });
        const url = req.urlData().path;
        if (req.query.page) {
            let page: number = parseInt(req.query.page);
            let follows = await Follower.find({
                order: {
                    created_at: 'DESC',
                },
                skip: ITEMS_ON_PAGE * (page - 1),
                take: ITEMS_ON_PAGE,
                where: conditions,
                relations: relationsToLoad
            });
            let actorUrls;
           if (!following) {
                actorUrls = follows.filter(follow => {
                    return !!follow.follower;
                }).map(follow => {
                    return follow.follower.getActorUrl()
                })
            } else {
                for (let following of follows) {
                    await following.loadActor();
                }
               actorUrls = follows.filter(follow => {
                   return !!follow.actor;
               }).map(follow => {
                   return follow.actor.getActorUrl()
               })
            }
            const pagesCount = Math.ceil(followsCount / ITEMS_ON_PAGE);
            return {
                '@context': context,
                id: `https://${config('server.domain')}${url}`,
                prev: page > 1 ? `https://${config('server.domain')}${url}?page=${page - 1}` : undefined,
                next: page < pagesCount ? `https://${config('server.domain')}${url}?page=${page + 1}` : undefined,
                type: 'OrderedCollectionPage',
                totalItems: followsCount,
                orderedItems: actorUrls
            }
        } else {
            return {
                '@context': context,
                id: `https://${config('server.domain')}${url}`,
                first: `https://${config('server.domain')}${url}?page=1`,
                type: 'OrderedCollection',
                totalItems: followsCount,
            }
        }
    }

    async function getOutboxCollection(req, conditions) {
        const streamsCount = await Stream.count({
            where: conditions,
         });
        const url = req.urlData().path;
        if (req.query.page) {
            let page: number = parseInt(req.query.page);
            let streams = await Stream.find({
                order: {
                    started_at: 'DESC',
                },
                skip: ITEMS_ON_PAGE * (page - 1),
                take: ITEMS_ON_PAGE,
                where: conditions,
                relations: ['channel', 'broadcaster']
            });
            let items = streams.map(stream => stream.toActivity('Create'))
            const pagesCount = Math.ceil(streamsCount / ITEMS_ON_PAGE);
            return {
                '@context': context,
                id: `https://${config('server.domain')}${url}`,
                prev: page > 1 ? `https://${config('server.domain')}${url}?page=${page - 1}` : undefined,
                next: page < pagesCount ? `https://${config('server.domain')}${url}?page=${page + 1}` : undefined,
                type: 'OrderedCollectionPage',
                totalItems: streamsCount,
                orderedItems: items
            }
        } else {
            return {
                '@context': context,
                id: `https://${config('server.domain')}${url}`,
                first: `https://${config('server.domain')}${url}?page=1`,
                type: 'OrderedCollection',
                totalItems: streamsCount,
            }
        }
    }

    fastify.get('/users/:login/followers', async (req, res): Promise<any> => {
        let user = await User.findOneOrFail({ login: req.params.login, domain: null });
        res.send(await getFollowCollection(req,{
            actor_id: user.id,
            actor_type: Follower.TYPE_CHANNEL
        }, false))
    });

    fastify.get('/channels/:url/followers', async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({ url: req.params.url, domain: null });
        res.send(await getFollowCollection(req,{
            actor_id: channel.id,
            actor_type: Follower.TYPE_CHANNEL
        }, false))
    });

    fastify.get('/users/:login/following', async (req, res): Promise<any> => {
        let user = await User.findOneOrFail({ login: req.params.login, domain: null });
        res.send(await getFollowCollection(req,{
            follower: {
                id: user.id
            }
        }, true))
    });

    fastify.get('/channels/:url/following', async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({ url: req.params.url, domain: null });
        res.send(await getFollowCollection(req,{
            follower: {
                id: -1 // send empty collection, channels can't follow anybody
            }
        }, true))
    });

    fastify.get('/users/:login/outbox', async (req, res): Promise<any> => {
        let user = await User.findOneOrFail({ login: req.params.login, domain: null });
        res.send(await getOutboxCollection(req,{
            broadcaster: {
                id: user.id
            }
        }))
    });

    fastify.get('/channels/:url/outbox', async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({ url: req.params.url, domain: null });
        res.send(await getOutboxCollection(req,{
            channel: {
                id: channel.id
            }
        }))
    });


    fastify.post('/shared-inbox', {
        config: {
            rawBody: true
        },
    }, async (req, res): Promise<any> => {
        let status = await handleInbox(req.headers, req.rawBody, '/api/federation/shared-inbox');
        if (status) {
            res.send({
                status: true
            })
        } else {
            res.status(400).send({
                status: false
            })
        }
    })

    fastify.post('/channels/:url/inbox', {
        config: {
            rawBody: true
        },
    }, async (req, res): Promise<any> => {
        let status = await handleInbox(req.headers, req.rawBody, `/api/federation/channels/${req.params.url}`);
        if (status) {
            res.send({
                status: true
            })
        } else {
            res.status(400).send({
                status: false
            })
        }
    })

    fastify.post('/users/:login/inbox', {
        config: {
            rawBody: true
        },
    }, async (req, res): Promise<any> => {
        let status = await handleInbox(req.headers, req.rawBody, `/api/federation/users/${req.params.login}`);
        if (status) {
            res.send({
                status: true
            })
        } else {
            res.status(400).send({
                status: false
            })
        }
    })

}
module.exports = routes;
