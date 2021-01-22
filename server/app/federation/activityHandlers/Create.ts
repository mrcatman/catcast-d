import { Stream } from '../../models/Stream'
import { Channel } from '../../models/Channel'
import { getActorByUrl } from '../remoteActor'
import { User } from '../../models/User'
import filterObject from '../../helpers/filterObject'
import { Picture } from '../../models/Picture'

export async function create(object) {

  if (!object || !object.catcastObjectType) {
    return;
  }
  console.log(object);
  if (object.catcastObjectType === 'Stream') {
    let stream = await Stream.findOne({
      object_id: object.id
    });
    if (!stream) {
      stream = new Stream();
      stream.object_id = object.id;
      stream.name = object.name;
      stream.started_at = object.published;
      stream.ended_at = object.endedAt;
      stream.watch_url = object.watchUrl;
      stream.cover_url = object.coverUrl;
      await stream.save();
      if (object.channel) {
        let channel = await getActorByUrl(object.channel);
        if (channel instanceof Channel) {
          stream.channel = channel;
          await stream.save();
          if (!object.endedAt) {
            channel.current_stream = stream;
            await channel.save();
          }
        }
      }
      if (object.broadcaster) {
        let broadcaster = await getActorByUrl(object.broadcaster);
        if (broadcaster instanceof User) {
          stream.broadcaster = broadcaster;
          await stream.save();
        }
      }
    }
  }
}

export async function update(object) {

  if (!object || (!object.catcastObjectType && !object.catcastActorType)) {
    return;
  }
  if (object.catcastObjectType === 'Stream') {
    return await updateStream(object);
  }
  if (object.catcastActorType === 'Channel') {
    return await updateChannel(object);
  }
  if (object.catcastActorType === 'User') {
    return await updateUser(object);
  }
  return false;
}

async function updateStream(object) {
  let stream = await Stream.findOne({
    object_id: object.id
  });
  console.log('update stream', stream, object);
  if (stream) {
    if (object.name) {
      stream.name = object.name;
    }
    if (object.watchUrl) {
      stream.watch_url = object.watchUrl;
    }
    if (object.coverUrl) {
      stream.cover_url = object.coverUrl;
    }
    if (object.published) {
      stream.started_at = object.published;
    }
    stream.ended_at = object.endedAt;
    await stream.save();
    return true;
  }
  return false;
}

async function updateChannel(object) {
  let channel = await Channel.findOne({
    actor_id: object.id
  });

  if (channel) {
    if (object.preferredUsername) {
      channel.name = object.preferredUsername;
    }
    if (object.summary) {
      channel.description = object.summary;
    }
    if (object.icon?.url && (!channel.logo || channel.logo.remote_url !== object.icon.url)) {
      let picture = new Picture();
      picture.fill({
        remote_url: object.icon.url
      })
      await picture.save();
      channel.logo = picture;
    }
    await channel.save();
    return true;
  }
  return false;
}

async function updateUser(object) {
  let user = await User.findOne({
    actor_id: object.id
  });
  if (user) {
    if (object.preferredUsername) {
      user.name = object.preferredUsername;
    }
    if (object.summary) {
      user.about = object.summary;
    }
    if (object.icon?.url && (!user.avatar || user.avatar.remote_url !== object.icon.url)) {
      let picture = new Picture();
      picture.fill({
        remote_url: object.icon.url
      })
      user.avatar = picture;
    }
    await user.save();
    return true;
  }
  return false;
}
