import { User } from '../../../app/models/User'
import { generateKeys } from '../../../app/federation/crypto'
import { Role } from '../../../app/helpers/roles'
const argon2 = require('argon2');

export default async function create({login, email, password}: {login: string, email: string, password: string}) {
  if (!login) {
    console.warn('Usage: users/create --login=login --email=email --password=password');
    return;
  }
  let user = await User.findOne({
    login
  });
  if (user) {
    throw new Error(`User with login ${login} already exists`);
  }
  user = await User.findOne({
    email
  });
  if (user) {
    throw new Error(`User with email ${email} already exists`);
  }
  if (!password || password.length < 8) {
    throw new Error(`Password too short (min. 8 characters)`);
  }
  if (!email || !/\S+@\S+\.\S+/.test(email)) {
    throw new Error(`Wrong email format`);
  }
  user = new User();
  user.login = login;
  user.email = email;
  user.password = await argon2.hash(password);
  let {publicKey, privateKey} = await generateKeys();
  user.public_key = publicKey;
  user.private_key = privateKey;
  user.role_id = Role.USER;
  await user.save();
  console.log(`Created user with login ${login}, email ${email} and password ${password}`);
}
