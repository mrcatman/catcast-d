export const getConfig = () => {
  const configPath = process.argv.length >= 3 ? process.argv[2] : 'app/config';
  return require('../../' + configPath);
}
