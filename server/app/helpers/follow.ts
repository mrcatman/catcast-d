import { User } from '../models/User'
import { Channel } from '../models/Channel'
import { Follower } from '../models/Follower'

export function getFollowConditionsChannel(user: User, channel: Channel) {
  return {
    follower: {
      id: user.id
    },
    actor_id: channel.id,
    actor_type: Follower.TYPE_CHANNEL
  }
}
export function getFollowConditionsUser(user: User, secondUser: User) {
  return {
    follower: {
      id: user.id
    },
    actor_id: secondUser.id,
    actor_type: Follower.TYPE_USER
  }
}
