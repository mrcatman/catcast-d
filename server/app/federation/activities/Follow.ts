import { Channel } from '../../models/Channel'
import { User } from '../../models/User'
import { sendActivityPubRequest } from '../sendActivityPubRequest'

export async function Follow(localActor: User, remoteActor: Channel | User): Promise<any> {
  await sendActivityPubRequest('Follow', {
    object: remoteActor.getActorUrl()
  }, localActor, [remoteActor]);
}

export async function Unfollow(localActor: User, remoteActor: Channel | User): Promise<any> {
  await sendActivityPubRequest('Undo', {
    object: {
      id: localActor.getActorUrl(`#follows/${localActor.id}`),
      type: 'Follow',
      actor: localActor.getActorUrl(),
      object: remoteActor.getActorUrl()
   }
  }, localActor, [remoteActor]);
}
