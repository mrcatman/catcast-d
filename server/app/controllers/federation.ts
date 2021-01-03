import {ServerInstance} from "../types";
import {User} from "../models/User";
import {SHARED_INBOX_URL} from "../federation/constants";
import {Channel} from "../models/Channel";
import { Follower } from '../models/Follower'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/users/:login', async (req, res): Promise<any> => { // get actor for a user
        let user = await User.findOneOrFail({ login: req.params.login }, {
            relations: ['avatar']
        });
        res.header('Content-Type', 'application/activity+json').send({
            '@context': [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
                {
                    'PropertyValue': 'schema:PropertyValue',
                    'schema': 'http://schema.org#',
                    'value': 'schema:value'
                },
            ],
            id: user.getActorUrl(),
            type: 'Person',
            following: user.getActorUrl('/following'),
            followers: user.getActorUrl('/followers'),
            inbox: user.getActorUrl('/inbox'),
            outbox: user.getActorUrl('/outbox'),
            preferredUsername: user.login,
            name: user.login,
            summary: user.about,
            url: user.getWebUrl(),
            publicKey: {
                id: user.getActorUrl() + '#key',
                owner: user.getActorUrl(),
                publicKeyPem: user.public_key
            },
            icon: {
                type: 'Image',
                mediaType: 'image/png',
                url: user.avatar?.full_url
            },
            endpoints: {
                sharedInbox: SHARED_INBOX_URL
            }
        });
    });

    fastify.get('/channels/:url', async (req, res): Promise<any> => { // get actor for a channel
        let channel = await Channel.findOneOrFail({ url: req.params.url }, {
            relations: ['logo']
        });
        res.header('Content-Type', 'application/activity+json').send({
            '@context': [
                'https://www.w3.org/ns/activitystreams',
                'https://w3id.org/security/v1',
                {
                    'PropertyValue': 'schema:PropertyValue',
                    'schema': 'http://schema.org#',
                    'value': 'schema:value'
                },
            ],
            id: channel.getActorUrl(),
            type: 'Group', // maybe change to something better?
            following: channel.getActorUrl('/following'),
            followers: channel.getActorUrl('/followers'),
            inbox: channel.getActorUrl('/inbox'),
            outbox: channel.getActorUrl('/outbox'),
            preferredUsername: channel.name,
            name: channel.url,
            summary: channel.description,
            url: channel.getWebUrl(),
            publicKey: {
                id: channel.getActorUrl() + '#key',
                owner: channel.getActorUrl(),
                publicKeyPem: channel.public_key
            },
            icon: {
                type: 'Image',
                mediaType: 'image/png',
                url: channel.logo?.full_url
            },
            endpoints: {
                sharedInbox: SHARED_INBOX_URL
            }
        });
    });

    async function getFollowersCollection(req, conditions) {
        let followers = await Follower.find({
            where: conditions,
            relations: ['follower']
        });
        let actorUrls = followers.filter(follower => {
            return !!follower.follower;
        }).map(follower => {
            return follower.follower.getActorUrl()
        })
        return {
            '@context': 'https://www.w3.org/ns/activitystreams',
            id: `https://${fastify.config.domain}${req.url}`,
            type: 'OrderedCollectionPage',
            totalItems: actorUrls.length,
            orderedItems: actorUrls
        }
    }

    fastify.get('/users/:login/followers', async (req, res): Promise<any> => {
        let user = await User.findOneOrFail({ login: req.params.login, domain: null });
        res.send(await getFollowersCollection(req,{
            actor_id: user.id,
            actor_type: Follower.TYPE_CHANNEL
        }))
    });

    fastify.get('/channels/:url/followers', async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({ url: req.params.url, domain: null });
        res.send(await getFollowersCollection(req,{
            actor_id: channel.id,
            actor_type: Follower.TYPE_CHANNEL
        }))
    });

    fastify.get('/users/:login/following', async (req, res): Promise<any> => {
        let user = await User.findOneOrFail({ login: req.params.login, domain: null });
        res.send(await getFollowersCollection(req,{
            follower: {
                id: user.id
            }
        }))
    });

    fastify.get('/channels/:url/following', async (req, res): Promise<any> => {
        let channel = await Channel.findOneOrFail({ url: req.params.url, domain: null });
        res.send(await getFollowersCollection(req,{
            follower: {
                id: -1 // send empty collection, channels can't follow anybody
            }
        }))
    });

}
module.exports = routes;
