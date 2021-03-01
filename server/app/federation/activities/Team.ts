import { Channel } from '../../models/Channel'
import { User } from '../../models/User'
import { sendActivityPubRequest } from '../sendActivityPubRequest'
import { UserPermissions } from '../../models/UserPermissions'

// request for joining the channel team

export async function Offer(permissions: UserPermissions): Promise<any> {
  await sendActivityPubRequest('Offer', {
    id: permissions.channel.getActorUrl(`/permissions/${permissions.user.activitypub_handle}`),
    object: permissions.toObject()
  }, permissions.channel, [permissions.user]);
}

export async function CancelOffer(permissions: UserPermissions): Promise<any> {
  await sendActivityPubRequest('Undo', {
    id: permissions.channel.getActorUrl(`/permissions/${permissions.user.activitypub_handle}/undo`),
    object: {
      id: permissions.channel.getActorUrl(`/permissions/${permissions.user.activitypub_handle}`),
      type: 'Offer',
      actor: permissions.channel.getActorUrl(),
      object: permissions.user.getActorUrl()
    }
  }, permissions.channel, [permissions.user]);
}
