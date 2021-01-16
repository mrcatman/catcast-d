import axios from '../helpers/axios';
import { Channel } from '../models/Channel'
import { User } from '../models/User'
import { MAX_PAGES_TO_FETCH } from './constants'
import { Stream } from '../models/Stream'
import { Picture } from '../models/Picture'
import { getConfig } from '../helpers/getConfig'
import { create } from './activityHandlers/Create'
const {parse: parseUrl} = require('url');
const config = getConfig();

export async function getActorByWebfinger(domain: string, resource: string) {
  const protocol = domain.indexOf('localhost') !== -1 ? 'http' : 'https';
  const webfingerUrl = `${protocol}://${domain}/.well-known/webfinger?resource=acct:${resource}@${domain}`;
  let res = (await axios.get(webfingerUrl)).data;
  if (res.links && res.links.length > 0) {
    let link = res.links.filter(link => link.type === 'application/activity+json')[0];
    if (link) {
      return await getRemoteActor(link);
    }
  }
}

export async function getActorByUrl(url: string): Promise<Channel | User | null> {
  let parsed = parseUrl(url);
  if (parsed.host === config.host) {
    let splitted = url.split('/');
    let identifier = splitted[splitted.length - 1];
    let actorType = splitted[splitted.length - 2];
    if (actorType === 'channels') {
      return await Channel.findOne({
        url: identifier
      });
    } else {
      return await User.findOne({
        login: identifier
      });
    }
  } else {
    let remoteActor = await getRemoteActor(url);
    let actor;
    if (remoteActor.catcastActorType === 'Channel') {
      actor = await Channel.findOne({
        url: remoteActor.name,
        domain: parsed.host
      })
    } else {
      actor = await User.findOne({
        login: remoteActor.name,
        domain: parsed.host
      })
    }
    if (!actor) {
      if (remoteActor.catcastActorType === 'Channel') {
        actor = new Channel();
      } else {
        actor = new User();
      }
      actor.domain = parsed.host;
      actor.key_id = remoteActor.publicKey.id;
      await fetchCommonInfo(remoteActor, actor);
    }
    return actor;
  }
}

export async function getRemoteActor(url: string): Promise<any> {
  let remoteActor = (await axios.get(url)).data; //todo: add checks and cache
  return remoteActor;
}


export async function fetchCommonInfo(remoteObject, localActor: Channel | User) {
  localActor.fill({
    name: remoteObject.preferredUsername,
    public_key: remoteObject.publicKey.publicKeyPem,
  })
  if (localActor instanceof Channel) {
    localActor = localActor as Channel;
    localActor.fill({
      url: remoteObject.name,
      description: remoteObject.summary,
    })
  } else {
    localActor = localActor as User;
    localActor.fill({
      login: remoteObject.name,
      about: remoteObject.summary,
    })
  }

  await localActor.save();
  if (remoteObject.icon && remoteObject.icon.url) {
    let picture = new Picture();
    picture.fill({
      remote_url: remoteObject.icon.url
    })
    await picture.save();
    if (localActor instanceof Channel) {
      localActor.logo = picture;
    } else {
      localActor.avatar = picture;
    }
  }
  if (remoteObject.followers) {
    let followersObject = (await axios.get(remoteObject.followers)).data;
    if (followersObject.totalItems) {
      localActor.followers_count = followersObject.totalItems;
    }
  }
  if (remoteObject.inbox) {
    localActor.inbox_url = remoteObject.inbox;
  }
  if (remoteObject.endpoints?.sharedInbox) {
    localActor.shared_inbox_url = remoteObject.endpoints.sharedInbox;
  }
  if (remoteObject.outbox) {
    localActor.outbox_url = remoteObject.outbox;
  }
  await localActor.save();
  if (remoteObject.outbox) {
    let outboxObject = (await axios.get(remoteObject.outbox)).data;
    if (outboxObject.type === 'OrderedCollection' && outboxObject.totalItems > 0) {
      let nextLink = outboxObject.first;
      for (let i = 0; i < MAX_PAGES_TO_FETCH; i++) {
        if (nextLink) {
          let outboxCollectionObject = (await axios.get(nextLink)).data;
          if (outboxCollectionObject.totalItems > 0 && outboxCollectionObject.orderedItems && outboxCollectionObject.orderedItems.length > 0) {
            for (let outboxItem of outboxCollectionObject.orderedItems) {
              if (outboxItem.type === 'Create' && outboxItem.to.includes('https://www.w3.org/ns/activitystreams#Public')) {
                create(outboxItem.object);
              }
            }
            nextLink = outboxCollectionObject.next;
          }
        }
      }
    }
  }
}
