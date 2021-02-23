import { Channel } from '../../models/Channel'
import { User } from '../../models/User'
import { sendActivityPubRequest } from '../sendActivityPubRequest'

// requests here are non-standard, obviously

export async function Connect(connectKey: string, user: User, channel: Channel): Promise<any> {
  await sendActivityPubRequest('ChatConnect', {
    id: user.getActorUrl(`#chat_connect/${channel.id}`),
    object: {
      object: {
        id: channel.getActorUrl(),
      },
      connect_key: connectKey
    }
  }, user, [channel]);
}

