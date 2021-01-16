import { User } from '../models/User'
import { createHash, createSign, createVerify } from 'crypto';
import validateUrl from './validateUrl'
import { Channel } from '../models/Channel'
const {parse: parseUrl} = require('url');

// adapted from https://github.com/pixelfed/pixelfed/blob/dev/app/Util/ActivityPub/HttpSignature.php

export interface SignatureData {
  headers: string,
  keyId: string,
  algorithm?: string,
  signature: string
}


export function headersToSign(url: string, digest: string | null) {
  const date = new Date();
  let parsedUrl = parseUrl(url);
  let headers = {
    '(request-target)': 'post ' + parsedUrl.pathname,
    'Date': date.toUTCString(),
    'Host': parsedUrl.host,
    'Accept': 'application/activity+json, application/json',
    'Content-Type': 'application/activity+json'
  };
  if(digest) {
    headers['Digest'] = 'SHA-256=' + digest;
  }
  return headers;
}

function headersToSigningString(headers) {
  let str = [];
  for (let key in headers) {
    str.push(key.toLowerCase() + ': ' + headers[key]);
  }
  return str.join('\n');
}

export async function httpSign(actor: Channel | User, url: string, body: any, headers = {}) {
  let digest;
  if (body) {
    digest = createHash('sha256').update(JSON.stringify(body)).digest('base64');
  }
  headers = {
    ...headers,
    ...headersToSign(url, digest)
  }
  let stringToSign = headersToSigningString(headers);
  let signedHeaders = Object.keys(headers).map(header => header.toLowerCase()).join(' ');

  const classType = actor instanceof Channel ? Channel : User;
  const key = (await classType.findOne({
    select: ['id', 'private_key'],
    where: {id: actor.id}
  })).private_key;

  const sign = createSign('SHA256');
  sign.write(stringToSign);
  const signature = sign.sign(key, 'base64');
  const signatureHeader = `keyId="${actor.getActorUrl('#main-key')}",headers="${signedHeaders}",algorithm="rsa-sha256",signature="${signature}"`;
  delete headers['(request-target)'];
  headers['Signature'] = signatureHeader;
  return headers;
}

export function parseSignatureHeader(signature: string): SignatureData | null {
  let parts = signature.split(',');
  let signatureData: any = {};
  parts.forEach(part => {
    let match = part.match(/(.+)="(.+)"/)
    if (match) {
      signatureData[match[1]] = match[2];
    }
  })
  if (!signatureData.keyId || !signatureData.headers || !signatureData.signature) {
    return;
  }
  if (!validateUrl(signatureData.keyId)) {
    return;
  }
  return signatureData;
}

export function verify(publicKey: string, signatureData: SignatureData, headers, path: string, body: string): [boolean, string] {
  const digest = `SHA-256=${createHash('sha256').update(body).digest('base64')}`;
   let headersToSign = {};
    signatureData.headers.split( ' ').forEach(header => {
    if (header == '(request-target)') {
      headersToSign[header] = `post ${path}`;
    } else {
      if(header == 'digest') {
        headersToSign[header] = digest;
      } else {
       if (headers[header]) {
         headersToSign[header] = headers[header];
       }
      }
    }
  });
  let signingString = headersToSigningString(headersToSign);
  const verifier = createVerify('SHA256');
  verifier.write(signingString);
  const verified = verifier.verify(publicKey, signatureData.signature, 'base64');
  return [verified, signingString];
}
