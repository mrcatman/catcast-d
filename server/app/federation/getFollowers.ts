import { Follower } from '../models/Follower'
import { Channel } from '../models/Channel'
import { User } from '../models/User'
import { Stream } from '../models/Stream'

interface followersList {
  channelFollowers: Array<User>,
  userFollowers: Array<User>,
}

export async function getChannelFollowers(channelId: number): Promise<Array<User>> {
  return (await Follower.find({
    where: {
      actor_type: Follower.TYPE_CHANNEL,
      actor_id: channelId,
    },
    relations: ['follower']
  })).map(follower => follower.follower).filter(follower => follower.inbox_url || follower.shared_inbox_url);
}

export async function getUserFollowers(userId: number): Promise<Array<User>> {
  return (await Follower.find({
    where: {
      actor_type: Follower.TYPE_USER,
      actor_id: userId,
    },
    relations: ['follower']
  })).map(follower => follower.follower).filter(follower => follower.inbox_url || follower.shared_inbox_url);
}

export async function getFollowersFromActor(actor: Channel | User) {
  if (actor instanceof Channel) {
    return getChannelFollowers(actor.id);
  } else {
    return getUserFollowers(actor.id);
  }
}

export async function getFollowersFromObject(object: Stream): Promise<followersList> {
  let channelFollowers = await getChannelFollowers(object.channel.id);
  let userFollowers = await getUserFollowers(object.broadcaster.id);
  return {
    channelFollowers,
    userFollowers
  }
}
