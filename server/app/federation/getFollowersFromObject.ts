import { Follower } from '../models/Follower'
import { Channel } from '../models/Channel'
import { User } from '../models/User'
import { Stream } from '../models/Stream'

interface followersList {
  channelFollowers: Array<User>,
  userFollowers: Array<User>,
}
export async function getFollowersFromObject(object: Stream): Promise<followersList> {
  let channelFollowers = (await Follower.find({
    where: {
      actor_type: Follower.TYPE_CHANNEL,
      actor_id: object.channel.id,
    },
    relations: ['follower']
  })).map(follower => follower.follower).filter(follower => follower.inbox_url || follower.shared_inbox_url);
  let userFollowers = (await Follower.find({
    where: {
      actor_type: Follower.TYPE_USER,
      actor_id: object.broadcaster.id,
    },
    relations: ['follower']
  })).map(follower => follower.follower).filter(follower => follower.inbox_url || follower.shared_inbox_url);
  return {
    channelFollowers,
    userFollowers
  }

}
