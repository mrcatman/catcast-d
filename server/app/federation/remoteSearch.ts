import { Channel } from '../models/Channel'
import { getActorByHandle } from './remoteActor'

export async function remoteSearch(urlToLoad: string): Promise<Channel | null> {
  try {
    let remoteObject = await getActorByHandle(urlToLoad);
    if (!remoteObject) {
      return null;
    }
    if (remoteObject.toObject() && remoteObject.toObject().catcastActorType === 'Channel') { // now searching only for channels
      return remoteObject as Channel;
    }
  } catch (e) {

  }
  return null;
}
