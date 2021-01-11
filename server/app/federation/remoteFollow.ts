import { Channel } from '../models/Channel'
import { User } from '../models/User'
import { httpSign } from './httpSignature'
const axios = require('axios');

import { useHttpOnLocalhost } from '../helpers/useHttpOnLocalhost'

export async function remoteFollow(localActor: User, remoteActor: Channel | User): Promise<any> {
  if (!remoteActor.domain || localActor.domain) {
    return;
  }
  let remoteUrl = remoteActor.shared_inbox_url || remoteActor.inbox_url;
  if (!remoteUrl) {
    return;
  }
  remoteUrl = useHttpOnLocalhost(remoteUrl);
  const payload = {
    '@context': 'https://www.w3.org/ns/activitystreams',
    'id': localActor.getActorUrl(`#follow/${localActor.id}`),
    'type': 'Follow',
    'actor': localActor.getActorUrl(),
    'object': remoteActor.getActorUrl()
  };
  const headers = await httpSign(localActor, remoteUrl, payload);
  axios({
    url: remoteUrl,
    method: 'POST',
    headers,
    data: payload
  }).then(res => res.data).then(res => {
    console.log(res);
  })
}

export async function remoteUnfollow(localActor: User, remoteActor: Channel | User): Promise<any> {
  if (!remoteActor.domain || localActor.domain) {
    return;
  }
  let remoteUrl = remoteActor.shared_inbox_url || remoteActor.inbox_url;
  if (!remoteUrl) {
    return;
  }
  remoteUrl = useHttpOnLocalhost(remoteUrl);
  const payload = {
    '@context': 'https://www.w3.org/ns/activitystreams',
    'id': localActor.getActorUrl(`#follow/${localActor.id}/undo`),
    'type': 'Undo',
    'actor': localActor.getActorUrl(),
    'object': {
      'id': localActor.getActorUrl(`#follows/${localActor.id}`),
      'type': 'Follow',
      'actor': localActor.getActorUrl(),
      'object': remoteActor.getActorUrl()
    }
  };
  const headers = await httpSign(localActor, remoteUrl, payload);
  axios({
    url: remoteUrl,
    method: 'POST',
    headers,
    data: payload
  }).then(res => res.data).then(res => {
    console.log(res);
  })
}
