import { Channel } from '../../models/Channel'
import { User } from '../../models/User'
import { sendActivityPubRequest } from '../sendActivityPubRequest'
import { UserBan } from '../../models/UserBan'

// request for joining the channel team

export async function Block(ban: UserBan): Promise<any> {
  await sendActivityPubRequest('Block', {
    object: ban.user.getActorUrl()
  }, ban.channel, [ban.user]);
}

export async function CancelBlock(ban: UserBan): Promise<any> {
  await sendActivityPubRequest('Undo', {
    id: ban.channel.getActorUrl(`/ban/${ban.user.activitypub_handle}/undo`),
    object: {
      id: ban.channel.getActorUrl(`/ban/${ban.user.activitypub_handle}`),
      type: 'Block',
      actor: ban.channel.getActorUrl(),
      object: ban.user.getActorUrl()
    }
  }, ban.channel, [ban.user]);
}
