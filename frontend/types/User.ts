import Picture from '~/types/Picture'
import { Role } from '~/helpers/roles'

export default interface User {
  id: number;
  email: string;
  login: string;
  about: string;
  last_time_seen: Date;
  picture: Picture | null;
  role_id: Role,
  activitypub_handle: string;
  web_url?: string;
  domain?: string;
  is_blocked?: boolean;
}

