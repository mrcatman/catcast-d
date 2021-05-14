var argv = require('minimist')(process.argv.slice(2));
let fs = require('fs');
let path = require('path');

let configFolder = argv.config_path || 'config';

let configValues = {};

export function config(key: string | null = null) {
  if (!key) {
    return configValues;
  }
  let keyPath = key.split('.');
  let namespace = keyPath.shift();
  let data;
  if (!configValues[namespace]) {
    let filePath = path.resolve(__dirname, `../${configFolder}/${namespace}.json`);
    if (fs.existsSync(filePath)) {
      try {
        data = JSON.parse(fs.readFileSync(filePath));
      } catch (e) {

      }
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

export const setConfig = (config) => {
  for (let namespace in config) {
    setConfigNamespace(namespace, config[namespace]);
  }
}

export const setConfigNamespace = (namespace, configValuesNamespace) => {
  configValues[namespace] = configValuesNamespace;
  fs.writeFileSync(`${configFolder}/${namespace}.json`, JSON.stringify(configValuesNamespace));
}


fs.readdir(configFolder, (err, files) => {
  for (let file of files) {
    let namespace = file.split(".")[0];
    try {
      configValues[namespace] = JSON.parse(fs.readFileSync(`${configFolder}/${file}`));
    } catch (e) {

    }
  }
});

