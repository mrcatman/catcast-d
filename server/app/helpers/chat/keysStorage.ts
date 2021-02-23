import { User } from '../../models/User'

interface keysUsersStorage {
  [key: string]: User;
}

class KeysStorage {
  storage: keysUsersStorage = {};
  add(key, user) {
    this.storage[key] = user;
  }
  get(key): User | null {
    return this.storage[key] ? this.storage[key] : null;
  }
}
let keysStorage = new KeysStorage();
export { keysStorage }
