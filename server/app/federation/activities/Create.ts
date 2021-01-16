import { Channel } from '../../models/Channel'
import { User } from '../../models/User'
import { sendActivityPubRequest } from '../sendActivityPubRequest'
import { Stream } from '../../models/Stream'
import { getFollowersFromObject } from '../getFollowersFromObject'

export async function Create(note: Stream): Promise<any> {
  let {channelFollowers, userFollowers} = await getFollowersFromObject(note);
  if (channelFollowers.length > 0) {
    await sendActivityPubRequest('Create', note.toActivity('Create'), note.channel, channelFollowers);
  }
  if (userFollowers.length > 0) {
    await sendActivityPubRequest('Create', note.toActivity('Create'), note.channel, userFollowers);
  }
}

export async function Update(note: Stream): Promise<any> {
  let {channelFollowers, userFollowers} = await getFollowersFromObject(note);
  if (channelFollowers.length > 0) {
    await sendActivityPubRequest('Update', note.toActivity('Update'), note.channel, channelFollowers);
  }
  if (userFollowers.length > 0) {
    await sendActivityPubRequest('Update', note.toActivity('Update'), note.channel, userFollowers);
  }
}
