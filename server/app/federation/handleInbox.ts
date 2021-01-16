import { verifySignature } from './verifySignature'
import validateUrl from './validateUrl'
import { follow, unfollow } from './activityHandlers/Follow'
import { getActorByUrl } from './remoteActor'
import { create, update } from './activityHandlers/Create'

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
    case 'Create':
      if (data.object && data.object.object) {
        let actor = await getActorByUrl(data.actor);
        if (actor) {
          let followersCount = await actor.followersCount();
          if (followersCount > 0) {
            create(data.object.object);
            status = true;
          }
        }
      }
      break;
    case 'Update':
      status = await update(data.object.object);
      break;
    default:
      console.log(`Unknown activity type: ${data.type}`);
      break;
  }
  return status;
}
