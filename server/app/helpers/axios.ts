import axios, { AxiosInstance } from 'axios';

const client: AxiosInstance = axios.create();
client.defaults.headers.common['Accept'] = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

client.interceptors.request.use(function (config) {
  if (config.url.indexOf('https://localhost') !== -1) { // disable SSL on localhost requests for simplicity
    config.url = config.url.replace('https://localhost', 'http://localhost')
  }

  return config
}, function (error) {
  return Promise.reject(error)
})


export default client;
