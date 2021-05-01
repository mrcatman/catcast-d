import { User } from '../../../app/models/User'
import { Role } from '../../../app/helpers/roles'


export default async function setRole({login, role}: {login: string, role: string}) {
  if (!login) {
    console.warn('Usage: users/set-role --login=login --role=role');
    const roles = Object.keys(Role).map(key => Role[key]).filter(value => typeof value === 'string') as string[];
    console.warn(`Available roles: ${roles.join(', ')}`);
    return;
  }
  const user = await User.findOne({
    login
  });
  if (!user) {
    throw new Error(`User with login ${login} not found`);
  }
  if (Role[role] === undefined) {
    throw new Error(`Role ${role} not found`);
  }
  user.role_id = Role[role];
  await user.save();
  console.log(`User ${login} now has role ${role}`);
}
