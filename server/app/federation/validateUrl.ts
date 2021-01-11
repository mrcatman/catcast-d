import { canFederateWith } from './canFederateWith'

const {parse: parseUrl} = require('url');
export default function validateUrl(url: string): boolean { // todo: add some more checks
  if (!url.match(/(?:^|\s)((https?:\/\/)?(?:localhost|[\w-]+(?:\.[\w-]+)+)(:\d+)?(\/\S*)?)/)) {
    return false;
  }
  let parsedUrl = parseUrl(url);
  if (!canFederateWith(parsedUrl.host)) {
    return false;
  }
  return true;
}
