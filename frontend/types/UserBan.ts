import User from '~/types/User'
export default interface UserBan {
  id?: number;
  comment?: string;
  user: User;
}
