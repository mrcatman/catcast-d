import { User } from '../app/models/User'

const argv = require('minimist')(process.argv.slice(2));
const fn = require(`./commands/${argv._}`).default;
import { config } from '../app/config'
import {createConnection} from "typeorm";
import { Channel } from '../app/models/Channel'
import { Follower } from '../app/models/Follower'
import { Picture } from '../app/models/Picture'
import { Stream } from '../app/models/Stream'
import { StreamKey } from '../app/models/StreamKey'

(async() => {
  const connection = await createConnection({
    ...config('database'),
    synchronize: true,
    extra: {
      "charset": "utf8mb4_unicode_ci"
    },
    entities: [
      User,
      Channel,
      Follower,
      Picture,
      Stream,
      StreamKey
    ]
  });

  if (fn) {
    await fn(argv);
    process.exit();
  }
})()

