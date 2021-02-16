import Picture from '~/types/Picture'
import { Role } from '~/helpers/roles'


export default interface User {
  email: string;
  login: string;
  about: string;
  last_time_seen: Date;
  picture: Picture | null;
  role_id: Role
}
