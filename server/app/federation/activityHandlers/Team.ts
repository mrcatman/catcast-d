import { getActorByUrl } from '../remoteActor'
import { Follower } from '../../models/Follower'
import { Channel } from '../../models/Channel'
import { UserPermissions } from '../../models/UserPermissions'
import { User } from '../../models/User'

export async function offer(channelUrl: string, object: any): Promise<boolean> {
  let channel = await getActorByUrl(channelUrl) as Channel;
  let user = await getActorByUrl(object.to) as User;
  if (!channel || !user) {
    return false;
  }
  if (object.catcastObjectType !== 'UserPermissions') {
    return;
  }
  if (!object.full && (!object.list || object.list.length === 0)) {
    return;
  }
  let permissions = await UserPermissions.findOne({
    channel: {
      id: channel.id
    },
    user: {
      id: user.id
    }
  });
  if (!permissions) {
    permissions = new UserPermissions();
    permissions.user = user;
    permissions.channel = channel;
    permissions.confirmed = false;
  }
  permissions.full = !!object.full;
  permissions.list = object.list ? object.list : [];
  permissions.comment = object.comment;
  permissions.created_at = object.published;
  await permissions.save();
  return true;
}


export async function cancelOffer(channelUrl: string, userUrl: string): Promise<boolean> {
  let channel = await getActorByUrl(channelUrl) as Channel;
  let user = await getActorByUrl(userUrl) as User;
  if (!channel || !user) {
    return false;
  }
  let permissions = await UserPermissions.findOne({
    channel: {
      id: channel.id
    },
    user: {
      id: user.id
    }
  });
  if (permissions) {
    await permissions.remove();
    return true;
  }
  return false;
}
