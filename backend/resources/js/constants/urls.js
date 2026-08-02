// Laravel serves the SPA, so the API and websockets share the page's origin.
export const API_URL = '/api/';
export const WEBSOCKETS_HOST = import.meta.client ? window.location.origin : '';
