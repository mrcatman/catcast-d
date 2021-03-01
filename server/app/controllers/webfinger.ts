import {ServerInstance} from "../types";
import {CHANNEL_ACTOR_PREFIX} from "../federation/constants";
import {Channel} from "../models/Channel";
import {User} from "../models/User";
import { config } from '../config'

async function routes (fastify: ServerInstance, options) {

    fastify.get('/webfinger',  async (req, res): Promise<any> => {
        let resource: string = req.query.resource;
        if (!resource || !resource.startsWith('acct:') || resource.split('@').length !== 2) {
            res.code(400).send({
                error: 'Bad request'
            })
        }
        let [account, domain] = resource.replace('acct:', '').split('@');
        if (domain !== config('server.domain')) {
            res.code(400).send({
                error: `The user does not belong to ${config('server.domain')} domain`
            })
        }
        if (account.startsWith(CHANNEL_ACTOR_PREFIX)) {
            account = account.replace(CHANNEL_ACTOR_PREFIX, '');
            let channel = await Channel.findOne({
                url: account,
                domain: null
            });
            if (channel) {
                res.send({
                    subject: resource,
                    links: [
                        {
                            rel: 'self',
                            type: 'application/activity+json',
                            href: channel.getActorUrl()
                        },
                        {
                            rel: 'http://webfinger.net/rel/profile-page',
                            type: 'text/html',
                            href: channel.getWebUrl()
                        }
                    ]
                })
            } else {
                res.status(404).send({
                    error: `Channel ${account}@${domain} not found`
                })
            }
        } else {
            let user = await User.findOne({
                login: account,
                domain: null
            });
            if (user) {
                res.send({
                    subject: resource,
                    links: [
                        {
                            rel: 'self',
                            type: 'application/activity+json',
                            href: user.getActorUrl()
                        },
                        {
                            rel: 'http://webfinger.net/rel/profile-page',
                            type: 'text/html',
                            href: user.getWebUrl()
                        }
                    ]
                })
            } else {
                res.status(404).send({
                    error: `User ${account}@${domain} not found`
                })
            }
        }
    })
}

module.exports = routes;
