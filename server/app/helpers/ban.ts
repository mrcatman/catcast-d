import { UserPermissions } from '../models/UserPermissions'
import { UserBan } from '../models/UserBan'
import { Block, CancelBlock } from '../federation/activities/Blocklist'
import { User } from '../models/User'
import { Channel } from '../models/Channel'

export async function ban(channel: Channel, userToBan: User, moderator: User, data: Partial<UserBan>) {
  if (userToBan.id === channel.owner.id) {
    throw {
      status: 422, errors: { user: ['dashboard.blocklist._errors.user_is_owner'] }
    };
  }
  let permissions = await UserPermissions.findOne({
    where: {
      channel: {
        id: channel.id
      },
      user: {
        id: userToBan.id
      }
    }
  });
  if (permissions) {
    throw {
      status: 422, errors: { user: ['dashboard.blocklist._errors.user_is_in_team'] }
    };
  }
  let ban = await UserBan.findOne({
    where: {
      channel: {
        id: channel.id
      },
      user: {
        id: userToBan.id
      }
    },
    relations: ['user', 'channel', 'blocked_by']
  });
  if (!ban) {
    ban = new UserBan();
    ban.user = userToBan;
    ban.channel = channel;
    ban.blocked_by = moderator;
  }
  ban.fill({
    comment: data.comment
  })
  await ban.save();
  Block(ban);
  return ban;
}

export async function unban(channel: Channel, userToUnban: User) {
  let ban = await UserBan.findOne({
    where: {
      channel: {
        id: channel.id
      },
      user: {
        id: userToUnban.id
      }
    },
  });
  if (ban) {
    await ban.remove();
    CancelBlock(ban);
    return true;
  }
  return false;

}
