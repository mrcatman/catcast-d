import User from '~/types/User'
export default interface UserPermissions {
  id?: number;
  full: boolean;
  permissions: Array<string>;
  user: User;
  confirmed: boolean;
  rejected: boolean;
}
