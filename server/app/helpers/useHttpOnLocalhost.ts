const url = require('url');
export const useHttpOnLocalhost = (link) => { //
  let parsed = url.parse(link);
  if (parsed.hostname === 'localhost') {
    link = link.replace('https://', 'http://');
  }
  return link;
}
