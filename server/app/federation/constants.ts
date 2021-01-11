import { getConfig } from '../helpers/getConfig'
const config = getConfig();

export const CHANNEL_ACTOR_PREFIX = 'channel_';
export const SHARED_INBOX_URL = `https://${config.domain}/api/federation/shared-inbox`;

export const ITEMS_ON_PAGE = 10;

export const MAX_PAGES_TO_FETCH = 1;
