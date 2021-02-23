import { getActorByUrl } from '../remoteActor'
import { keysStorage } from '../../helpers/chat/keysStorage'

export async function connect(followerUrl: string, followingUrl: string, connectKey: string): Promise<boolean> {
  let user = await getActorByUrl(followerUrl);
  let channel = await getActorByUrl(followingUrl);
  if (!user || !channel || !user.domain || channel.domain) {
    return false;
  }
  keysStorage.add(connectKey, user);
  return true;
}
