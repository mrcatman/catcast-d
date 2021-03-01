import axios from '../helpers/axios';
import { Channel } from '../models/Channel'
import { User } from '../models/User'
import { MAX_PAGES_TO_FETCH } from './constants'
import { Picture } from '../models/Picture'
import { config } from '../config'
import { create } from './activityHandlers/Create'
import { canFederateWith } from './canFederateWith'

const {parse: parseUrl} = require('url');


export async function getActorByHandle(handle: string, webfingerOnly: boolean = false): Promise<Channel | User | null> {
  if (handle.indexOf('@') === -1 && handle.indexOf('/') === -1) {
    return;
  }
  let domain, resource;
  if (handle.indexOf('@') !== -1) {
    let splitted = handle.split('@');
    resource = splitted[0];
    domain = splitted[1];
  } else {
    if (!webfingerOnly) {
      let parsedUrl = parseUrl(handle);
      if (parsedUrl && parsedUrl.protocol !== 'http:' && parsedUrl.protocol !== 'https:') {
        handle = `https://${handle}`;
      }
      parsedUrl = parseUrl(handle);
      domain = parsedUrl.host;
      resource = `channel_${parsedUrl.pathname.substring(1)}`;
    }
  }
  if (!canFederateWith(domain)) {
    return;
  }
  if (!domain || !resource) {
    return;
  }
  return await getActorByWebfinger(domain, resource);
}

export async function createActor(url: string, host: string) {
  let remoteActor = await getRemoteActor(url);
  let actor;
  if (remoteActor.catcastActorType === 'Channel') {
    actor = await Channel.findOne({
      url: remoteActor.name,
      domain: host
    })
  } else {
    actor = await User.findOne({
      login: remoteActor.name,
      domain: host
    })
  }
  if (!actor) {
    if (remoteActor.catcastActorType === 'Channel') {
      actor = new Channel();
    } else {
      actor = new User();
    }
    actor.actor_id = url;
    actor.domain = host;
    actor.key_id = remoteActor.publicKey.id;
    await fetchCommonInfo(remoteActor, actor);
  }
}


export async function getActorByUrl(url: string): Promise<Channel | User | null> {
  let parsed = parseUrl(url);
  if (parsed.host === config('server.domain')) {
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
      actor.actor_id = url;
      actor.domain = parsed.host;
      actor.key_id = remoteActor.publicKey.id;
      await fetchCommonInfo(remoteActor, actor);
    }
    return actor;
  }
}

export async function getActorByWebfinger(domain: string, resource: string): Promise<Channel | User | null> {
  const protocol = domain.indexOf('localhost') !== -1 ? 'http' : 'https';
  const webfingerUrl = `${protocol}://${domain}/.well-known/webfinger?resource=acct:${resource}@${domain}`;
  let res = (await axios.get(webfingerUrl)).data;
  if (res.links && res.links.length > 0) {
    let link = res.links.filter(link => link.rel === 'self')[0];
    if (link) {
      return await getActorByUrl(link.href);
    }
  }
  return null;
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
