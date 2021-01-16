import { Stream } from '../../models/Stream'
import { Channel } from '../../models/Channel'
import { getActorByUrl } from '../remoteActor'
import { User } from '../../models/User'
import filterObject from '../../helpers/filterObject'

export async function create(object) {
  if (!object || !object.catcastObjectType) {
    return;
  }
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
  if (!object || !object.catcastObjectType) {
    return;
  }
  if (object.catcastObjectType === 'Stream') {
    let stream = await Stream.findOne({
      object_id: object.id
    });
    if (stream) {
      if (object.name) {
        stream.name = object.name;
      }
      if (object.published) {
        stream.started_at = object.published;
      }
      if (object.endedAt) {
        stream.ended_at = object.endedAt;
      }
      await stream.save();
      return true;
    }
  }
  return false;
}
