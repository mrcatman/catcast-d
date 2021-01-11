import { verifySignature } from './verifySignature'
import validateUrl from './validateUrl'
import { follow, unfollow } from './activities/Follow'

export async function handleInbox(headers, body, path) {
  if (await verifySignature(headers, body, path)) {
    return await handleInboxActivity(JSON.parse(body));
  } else {
    return false;
  }
}

async function handleInboxActivity(data) {
  if (!data['@context'] || !data.type ||  !data.id || !data.actor || !data.object) {
    return;
  }
  let status;
  switch (data.type) {
    case 'Follow':
      status = await follow(data.actor, data.object);
      break;
    case 'Undo':
      if (!data.object.type) {
        status = false;
      }
      switch (data.object.type) {
        case 'Follow':
          status = await unfollow(data.actor, data.object?.object);
          break;
        default:
          break;
      }
      break;
    default:
      break;
  }
  return status;
}
