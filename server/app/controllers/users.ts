import {ServerInstance} from "../types";
import { In, Like } from 'typeorm'
import { User } from '../models/User'
import { UserPermissions } from '../models/UserPermissions'
import { Follower } from '../models/Follower'
import {getFollowConditionsUser} from '../helpers/follow'
import { Follow, Unfollow } from '../federation/activities/Follow'
import { Channel } from '../models/Channel'

async function routes (fastify: ServerInstance, options) {

  fastify.get('/search', async (req, res) => {
      let query = req.query.q || "";
      let users = await User.paginate({
          where: [
              {name: Like(`%${query}%`)},
              {login: Like(`%${query}%`)}
          ]
      }, req);
      res.send(users);
  })


  fastify.get('/:handle', async (req, res) => {
    let user = await User.findOneOrFail({
        where: [
            { id: req.params.handle },
            { login: req.params.handle }
        ]
    })
    res.send({user});
  });

  fastify.get('/:id/teams', async (req, res): Promise<any> => {
    let ownerChannels = (await Channel.find({
      where: {owner: { id: req.params.id }}
    })).map(channel => {
      return {
        channel,
        owner: true,
      }
    })
    let teams = (await UserPermissions.find({
      where: {
        user: {
          id: req.params.id
        },
        confirmed: true,
        hidden: false
      },
      relations: ['channel']
    })).filter(permission => permission.channel).map(permission => {
      return {
        channel: permission.channel,
        owner: false,
        comment: permission.comment
      }
    });
    res.send({
      teams: [...ownerChannels, ...teams],
    });
  })

  fastify.post('/:id/subscribe', {
    preValidation: [fastify.authenticate]
  }, async (req, res): Promise<any> => {
    let user = await User.findOneOrFail({id: req.params.id});
    if (user.id === req.user.id) {
      throw {
        statusCode: 403,
        text: 'subscribe_block.errors.cannot_subscribe_to_self'
      }
    }
    let subscription = await Follower.findOne({
      follower: {
        id: req.user.id
      },
      actor_id: user.id,
      actor_type: Follower.TYPE_USER
    })
    if (!subscription) {
      subscription = new Follower();
      subscription.fill(getFollowConditionsUser(req.user, user));
      await subscription.save();
    }
    if (user.domain) {
      Follow(req.user, user);
    }
    res.send({
      status: true
    });
  })

  fastify.post('/:id/unsubscribe', {
    preValidation: [fastify.authenticate]
  }, async (req, res): Promise<any> => {
    let user = await User.findOneOrFail({id: req.params.id});

    let subscription = await Follower.findOne(getFollowConditionsUser(req.user, user))
    if (subscription) {
      await subscription.remove();
    }
    if (user.domain) {
      Unfollow(req.user, user);
    }
    res.send({
      status: true
    });
  })

  fastify.get('/:id/subscribers-count', {
    preValidation: [fastify.authenticate_optional]
  }, async (req, res): Promise<any> => {
    let user = await User.findOneOrFail({id: req.params.id});

    let subscribers_count = (await Follower.find({
      actor_id: user.id,
      actor_type: Follower.TYPE_USER
    })).length;
    if (user.followers_count) {
      subscribers_count+= user.followers_count;
    }
    let is_subscribed = req.user ? !!(await Follower.findOne(getFollowConditionsUser(req.user, user))) : false;
    res.send({
      subscribers_count,
      is_subscribed
    });
  })


}

module.exports = routes;
