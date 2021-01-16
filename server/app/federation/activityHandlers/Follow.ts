import { getActorByUrl } from '../remoteActor'
import { Follower } from '../../models/Follower'
import { Channel } from '../../models/Channel'

export async function follow(followerUrl: string, followingUrl: string): Promise<boolean> {
  let follower = await getActorByUrl(followerUrl);
  let following = await getActorByUrl(followingUrl);
  if (!follower || !following) {
    return false;
  }
  const conditions = {
    follower: {
      id: follower.id
    },
    actor_id: following.id,
    actor_type: following instanceof Channel ? Follower.TYPE_CHANNEL : Follower.TYPE_USER
  };
  let subscription = await Follower.findOne(conditions);
  if (!subscription) {
    subscription = new Follower();
    subscription.fill(conditions);
    await subscription.save();
  }
  return true;
}

export async function unfollow(followerUrl: string, followingUrl: string): Promise<boolean> {
  let follower = await getActorByUrl(followerUrl);
  let following = await getActorByUrl(followingUrl);
  if (!follower || !following) {
    return false;
  }
  const conditions = {
    follower: {
      id: follower.id
    },
    actor_id: following.id,
    actor_type: following instanceof Channel ? Follower.TYPE_CHANNEL : Follower.TYPE_USER
  };
  let subscription = await Follower.findOne(conditions);
  if (subscription) {
    await subscription.remove();
  }
  return true;
}
