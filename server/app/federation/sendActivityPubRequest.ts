import { User } from '../models/User'
import { Channel } from '../models/Channel'
import { httpSign } from './httpSignature'
import axios from '../helpers/axios';

export async function sendActivityPubRequest(type: String, params, localActor: Channel | User, remoteActors: Array<Channel | User>): Promise<any> {
  if (localActor.domain) {
    return;
  }
  const payload = {
    '@context': 'https://www.w3.org/ns/activitystreams',
    type,
    id: localActor.getActorUrl(`#follow/${localActor.id}/undo`),
    actor: localActor.getActorUrl(),
    ...params,
  };
  let remoteUrls: Array<string> = [];
  for (let remoteActor of remoteActors) {
    if (remoteActor.domain) {
      let remoteUrl = remoteActor.shared_inbox_url || remoteActor.inbox_url;
      if (remoteUrl && !remoteUrls.includes(remoteUrl)) {
        remoteUrls.push(remoteUrl);
      }
    }
  }
  for (let remoteUrl of remoteUrls) {
    const headers = await httpSign(localActor, remoteUrl, payload);
    await axios({
      url: remoteUrl,
      method: 'POST',
      headers,
      data: payload
    })
  }
}
