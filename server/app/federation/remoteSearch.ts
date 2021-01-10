import { Picture } from '../models/Picture'

const url = require('url');
const axios = require('axios');
axios.defaults.headers.common['Accept'] = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

import { Channel } from '../models/Channel'
import { MAX_PAGES_TO_FETCH } from './constants'
import { Stream } from '../models/Stream'
import { canFederateWith } from './canFederateWith'

const useHttpOnLocalhost = (link) => {
  let parsed = url.parse(link);
  if (parsed.hostname === 'localhost') {
    link = link.replace('https://', 'http://');
  }
  return link;
}

export async function remoteSearch(urlToLoad: string): Promise<Channel> {
  let parsedUrl = url.parse(urlToLoad);
  if (parsedUrl && parsedUrl.protocol !== 'http:' && parsedUrl.protocol !== 'https:') {
    urlToLoad = `https://${urlToLoad}`;
  }
  let localChannel;
  parsedUrl = url.parse(urlToLoad);
  let domain = parsedUrl.host;
  if (!canFederateWith(domain)) {
    return;
  }
  let channelName = parsedUrl.pathname.substring(1);
  let protocol = parsedUrl.hostname === 'localhost' ? 'http' : 'https';
  let webfingerUrl = `${protocol}://${domain}/.well-known/webfinger?resource=acct:channel_${channelName}@${domain}`;
  try {
    let res = (await axios.get(webfingerUrl)).data;
    if (res.links && res.links.length > 0) {
      let link = res.links.filter(link => link.type === 'application/activity+json')[0];
      if (link) {
        let channelObject = (await axios.get(useHttpOnLocalhost(link.href))).data;
        if (channelObject.catcastActorType === 'Channel') { // now searching only for channels
          localChannel = await Channel.findOne({
            url: channelObject.name,
            domain: domain
          })
          if (localChannel) {
            return localChannel;
          }
          localChannel = new Channel();
          localChannel.fill({
            name: channelObject.preferredUsername,
            url: channelObject.name,
            description: channelObject.summary,
            public_key: channelObject.publicKey.publicKeyPem,
            domain: domain
          })

          await localChannel.save();
          if (channelObject.icon && channelObject.icon.url) {
            let logo = new Picture();
            logo.fill({
              remote_url: channelObject.icon.url
            })
            await logo.save();
            localChannel.logo = logo;
          }
          if (channelObject.followers) {
            let followersObject = (await axios.get(useHttpOnLocalhost(channelObject.followers))).data;
            if (followersObject.totalItems) {
              localChannel.followers_count = followersObject.totalItems;
            }
          }
          if (channelObject.inbox) {
            localChannel.inbox_url = channelObject.inbox;
          }
          if (channelObject.endpoints?.sharedInbox) {
            localChannel.shared_inbox_url = channelObject.endpoints.sharedInbox;
          }
          if (channelObject.outbox) {
            localChannel.outbox_url = channelObject.outbox;
          }
          await localChannel.save();
          if (channelObject.outbox) {
            let outboxObject = (await axios.get(useHttpOnLocalhost(channelObject.outbox))).data;
            if (outboxObject.type === 'OrderedCollection' && outboxObject.totalItems > 0) {
              let nextLink = outboxObject.first;
              for (let i = 0; i < MAX_PAGES_TO_FETCH; i++) {
                if (nextLink) {
                  let outboxCollectionObject = (await axios.get(useHttpOnLocalhost(nextLink))).data;
                  if (outboxCollectionObject.totalItems > 0 && outboxCollectionObject.orderedItems && outboxCollectionObject.orderedItems.length > 0) {
                    for (let outboxItem of outboxCollectionObject.orderedItems) {
                      if (outboxItem.type === 'Create' && outboxItem.to.includes('https://www.w3.org/ns/activitystreams#Public')) {
                        let outboxObject = outboxItem.object;
                        if (outboxObject.catcastObjectType === 'Stream') { // now saving only streams, without fetching users
                          let localStream = new Stream();
                          localStream.channel = localChannel;
                          localStream.name = outboxObject.name;
                          localStream.started_at = outboxObject.published;
                          localStream.ended_at = outboxObject.endedAt;
                          await localStream.save();
                          if (!outboxObject.endedAt) {
                            localChannel.is_online = true;
                            localChannel.current_stream = localStream;
                            await localChannel.save();
                            localChannel.current_stream.channel = undefined;  // prevent circular JSON
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
          return localChannel;

        }
      }
    }
  } catch (e) {
    console.log(e);
    if (localChannel) {
      return localChannel;
    }
  }
  return null;
}
