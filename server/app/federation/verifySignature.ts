import { parseSignatureHeader, verify } from './httpSignature'
import validateUrl from './validateUrl'
import { User } from '../models/User'
import { fetchCommonInfo, getActorByUrl, getRemoteActor } from './remoteActor'
const {parse: parseUrl} = require('url');

export async function verifySignature(headers: any, body: string, path: string): Promise<boolean> {
  let bodyDecoded = JSON.parse(body);
  let signature = Array.isArray(headers.signature) ? headers.signature[0] : headers.signature;
  let date = Array.isArray(headers.date) ? headers.date[0] : headers.date;
  if(!signature) {
    return false;
  }
  if(!date) {
    return false;
  }
  let dateObject = new Date(date);
  if (Math.abs(dateObject.getTime() - (new Date().getTime())) > 86400) {
    return false;
  }
  let signatureData = parseSignatureHeader(signature);
  if (!validateUrl(signatureData.keyId) || !validateUrl(bodyDecoded.id)) {
    return false;
  }
  let keyDomain = parseUrl(signatureData.keyId).host;
  let idDomain = parseUrl(bodyDecoded.id).host;
  if(bodyDecoded.object && bodyDecoded.object.attributedTo) {
    if(parseUrl(bodyDecoded.object.attributedTo).host !== keyDomain) {
      return false;
    }
  }
  if(!keyDomain || !idDomain || keyDomain !== idDomain) {
    return false;
  }

  let actor = await User.findOne({key_id: signatureData.keyId});
  if(!actor) {
    try {
      let actorUrl = Array.isArray(bodyDecoded.actor) ? bodyDecoded.actor[0] : bodyDecoded['actor'];
      actor = await getActorByUrl(actorUrl) as User;
    } catch (e) {
      console.log(e);
    }
  }
  if(!actor) {
    return false;
  }
  let [verified] = verify(actor.public_key, signatureData, headers, path, body);
  return verified;
}
