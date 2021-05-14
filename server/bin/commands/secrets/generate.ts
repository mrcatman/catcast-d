import { config, setConfigNamespace } from '../../../app/config'
const {randomBytes} = require('crypto');

export default async function generate() {
  const secretsList = ['cookie', 'jwt'];
  let secretsConfig = config('secrets') || {};
  for (let key of secretsList) {
    if (secretsConfig[key] && secretsConfig[key].length > 0) {
      console.log(`Not generating ${key} secret (already set)`);
    } else {
      secretsConfig[key] = randomBytes(16).toString('hex');
      console.log(`Generated secret for ${key}`);
    }
  }
  setConfigNamespace('secrets', secretsConfig);

}
