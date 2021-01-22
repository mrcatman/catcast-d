var argv = require('minimist')(process.argv.slice(2));
let fs = require('fs');
let path = require('path');

let configFolder = argv.config_path || 'config';

let configValues = {};

export function config(key: string) {
  let keyPath = key.split('.');
  let namespace = keyPath.shift();
  let data;
  if (!configValues[namespace]) {
    let filePath = path.resolve(__dirname, `../${configFolder}/${namespace}.json`);
    if (fs.existsSync(filePath)) {
      data = JSON.parse(fs.readFileSync(filePath));
      configValues[data] = data;
    }
  } else {
    data = configValues[namespace];
  }
  if (data) {
    for (let i = 0; i < keyPath.length; i++) {
      data = data[keyPath[i]];
    }
    return data;
  }
  return null;
}
