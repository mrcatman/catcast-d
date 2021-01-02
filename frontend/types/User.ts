import Picture from '~/types/Picture'

export default interface User {
  email: string;
  login: string;
  about: string;
  last_time_seen: Date;
  picture: Picture | null;
}
