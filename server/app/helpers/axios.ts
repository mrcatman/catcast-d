import axios, { AxiosInstance } from 'axios';

const client: AxiosInstance = axios.create();
client.defaults.headers.common['Accept'] = 'application/ld+json; profile="https://www.w3.org/ns/activitystreams"';

export default client;
