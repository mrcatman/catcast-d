const url = require('url');
import { Channel } from '../models/Channel'
import { canFederateWith } from './canFederateWith'
import {getActorByWebfinger, fetchCommonInfo} from './remoteActor'

export async function remoteSearch(urlToLoad: string): Promise<Channel | null> {
  if (urlToLoad.indexOf('@') === -1 && urlToLoad.indexOf('/') === -1) {
    return;
  }
  let localChannel, domain, resource;
  if (urlToLoad.indexOf('@') !== -1) {
    let splitted = urlToLoad.split('@');
    resource = splitted[0];
    domain = splitted[1];
  } else {
    let parsedUrl = url.parse(urlToLoad);
    if (parsedUrl && parsedUrl.protocol !== 'http:' && parsedUrl.protocol !== 'https:') {
      urlToLoad = `https://${urlToLoad}`;
    }
    parsedUrl = url.parse(urlToLoad);
    domain = parsedUrl.host;
    resource = `channel_${parsedUrl.pathname.substring(1)}`;
  }
  if (!canFederateWith(domain)) {
    return;
  }

  try {
    let remoteObject = await getActorByWebfinger(domain, resource);
    if (remoteObject.catcastActorType === 'Channel') { // now searching only for channels
      localChannel = await Channel.findOne({
        url: remoteObject.name,
        domain: domain
      })
      if (localChannel) {
        return localChannel;
      }
      localChannel = new Channel();
      localChannel.domain = domain;
      await fetchCommonInfo(remoteObject, localChannel);
      return localChannel;
    }
  } catch (e) {
    console.log(e);
    if (localChannel) {
      return localChannel;
    }
  }
  return null;
}
