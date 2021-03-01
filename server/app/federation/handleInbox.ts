import { verifySignature } from './verifySignature'
import { follow, unfollow } from './activityHandlers/Follow'
import { getActorByUrl } from './remoteActor'
import { create, update } from './activityHandlers/Create'
import { connect } from './activityHandlers/Chat'
import { cancelOffer, offer } from './activityHandlers/Team'

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
      console.log(data);
      if (!data.object.type) {
        status = false;
      }
      switch (data.object.type) {
        case 'Follow':
          status = await unfollow(data.actor, data.object?.object);
          break;
        case 'Offer':
          status = await cancelOffer(data.actor, data.object?.object);
          break;
        default:
          console.log(`Unknown activity undo type: ${data.object.type}`);
          break;
      }
      break;
    case 'Create':
      if (data.object) {
        let actor = await getActorByUrl(data.actor);
        if (actor) {
          let followersCount = await actor.followersCount();
          if (followersCount > 0) {
            create(data.object);
            status = true;
          }
        }
      }
      break;
    case 'Update':
      status = await update(data.object);
      break;
    case 'ChatConnect':
      status = await connect(data.actor, data.object.id, data.object.connect_key);
      break;
    case 'Offer':
      status = await offer(data.actor, data.object);
      break;
    default:
      console.log(`Unknown activity type: ${data.type}`);
      break;
  }
  return status;
}
