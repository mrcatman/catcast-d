import { User } from '../../../app/models/User'
import { Role } from '../../../app/helpers/roles'


export default async function makeAdmin({login, role}: {login: string, role: string}) {
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
