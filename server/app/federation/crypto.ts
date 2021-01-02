import { generateKeyPair } from 'crypto';
const { promisify } = require('util')
const generateKeyPairPromise = promisify(generateKeyPair)

interface keys {
    publicKey: string,
    privateKey: string
}
export function generateKeys() {
    return generateKeyPairPromise('rsa', {
        modulusLength: 4096,
        publicKeyEncoding: {
            type: 'spki',
            format: 'pem'
        },
        privateKeyEncoding: {
            type: 'pkcs8',
            format: 'pem'
        }
    }).then(keys => {
       return keys;
    });
}
