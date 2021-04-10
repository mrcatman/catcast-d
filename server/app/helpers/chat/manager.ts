import { User } from '../../models/User'
import { Channel } from '../../models/Channel'
import { UserChannelPermissions } from '../permissions/list'
import { UserPermissions } from '../../models/UserPermissions'
import { ChatMessage } from '../../models/ChatMessage'
import { ChatSettings } from '../../models/ChatSettings'
export type ChatUserInfo = {
  id: number;
  login: string;
  domain?: string;
  avatar?: string;
  sockets?: Array<any>;
  isModerator: boolean;
  isAdmin: boolean;
  roleName?: string;
  webUrl?: string;
}

export type ChatMessageInfo = {
  id: number,
  timestamp: number,
  author: ChatUserInfo,
  content: String,
}

export type UsersList = {
  [key: number]: Array<ChatUserInfo>;
}
export type MessagesList = {
  [key: number]: Array<ChatMessageInfo>;
}
export type ChatSettingsList = {
  [key: number]: ChatSettings;
}
export type GuestsList = {
  [key: number]: Array<any>;
}
export enum ChatClientUpdateType {
  SEND_MESSAGE = 'SEND_MESSAGE',
  DELETE_MESSAGE = 'DELETE_MESSAGE'
}



class ChatManager {
  users = {} as UsersList;
  guests = {} as GuestsList;
  messages = {} as MessagesList;
  settings = {} as ChatSettingsList

  sendUpdate(socket: any, type: String, payload: any = null) {
    socket.send(JSON.stringify({
      type,
      payload
    }))
  }


  async createUserInfoObject(channel: Channel, user: User): Promise<ChatUserInfo> {
    let isModerator, isAdmin = false;
    let roleName;
    if (channel.owner && channel.owner.id === user.id) {
      isAdmin = true;
    }
    let permissions = await UserPermissions.findOne({
      where: {
        confirmed: true, rejected: false, channel: { id: channel.id }, user: { id: user.id }
      }
    })
    isModerator = permissions && permissions.list.indexOf(UserChannelPermissions.MODERATE_CHAT) !== -1;
    roleName = permissions ? permissions.comment : '';

    const webUrl = user.domain ? user.web_url : user.getWebUrl();
    return {
      id: user.id,
      login: user.login,
      domain: user.domain,
      avatar: user.avatar ? user.avatar.full_url : null,
      isModerator,
      isAdmin,
      roleName,
      webUrl
    }
  }


  sendUpdateToRoom(channelId: number, type: String, payload: any = null) {
    let sockets = this.getSockets(channelId);

    sockets.forEach(socket => {
      socket.send(JSON.stringify({
        type,
        payload
      }))
    })
  }

  async addUser(channel: Channel, user: User | null, socket: any): Promise<ChatUserInfo | null> {
    let userInfo;
    const channelId = channel.id;
    if (user) {
       userInfo = await this.createUserInfoObject(channel, user);
      if (!this.users[channelId]) {
        this.users[channelId] = [];
      }
      let existingUser = this.users[channelId].filter(user => user.id === userInfo.id)[0];
      if (existingUser) {
        existingUser.sockets.push(socket);
      } else {
        userInfo.sockets = [socket];
        this.users[channelId].push(userInfo);
      }
      this.sendUpdateToRoom(channelId, 'USER_JOINED', {user: this.getVisibleUserInfo(userInfo)});
    } else {
      if (!this.guests[channelId]) {
        this.guests[channelId] = [];
      }
      this.guests[channelId].push(socket);
    }

    this.sendUpdate(socket, 'CONNECTED', {user: this.getVisibleUserInfo(userInfo)});

    const settings = await this.getSettings(channelId);
    this.sendUpdate(socket, 'CHAT_SETTINGS', {settings});

    const messages = await this.getMessages(channel);
    this.sendUpdate(socket, 'MESSAGES_LIST', {messages});

    let users = this.getUsers(channelId);
    this.sendUpdate(socket, 'USERS_LIST', {users});
    this.sendUpdatedMembersCount(socket, channelId);

    if (user) {
      socket.on('message', (data) => {
        data = JSON.parse(data);

        if (data && data.type) {
          this.handleUpdate(channel, userInfo, { type: data.type, payload: data.payload });
        }
      });
      socket.on('close', () => {
        this.deleteUser(channelId, userInfo);
      });
    } else {
      socket.on('close', () => {
        this.deleteGuest(channelId, socket);
      });
    }
    return userInfo;
  }

  sendUpdatedMembersCount(socket: any, channelId: number): void {
    let count = (this.users[channelId] ? this.users[channelId].length : 0) + (this.guests[channelId] ? this.guests[channelId].length : 0);
    this.sendUpdate(socket, 'MEMBERS_COUNT', {count});
  }

  deleteUser(channelId: number, userInfo: ChatUserInfo): void {
    this.users[channelId] = this.users[channelId].filter(user => user.id !== userInfo.id)
    this.sendUpdateToRoom(channelId, 'USER_LEFT', {user: this.getVisibleUserInfo(userInfo)});
  }

  deleteGuest(channelId: number, userSocket: any): void {
    this.guests[channelId] = this.guests[channelId].filter(socket => socket.id !== userSocket.id)
  }

  getVisibleUserInfo(user: ChatUserInfo): any {
    return {
      id: user.id,
      login: user.login,
      domain: user.domain,
      avatar: user.avatar,
      isModerator: user.isModerator,
      isAdmin: user.isAdmin,
      roleName: user.roleName,
      webUrl: user.webUrl
    };
  }

  getUsers(channelId: number): Array<ChatUserInfo> {
    return this.users[channelId] ? this.users[channelId].map(user => {
        return this.getVisibleUserInfo(user)
    }) : [];
  }

  async getSettings(channelId: number): Promise<ChatSettings> {
    if (!this.settings[channelId]) {
      let settings = await ChatSettings.findOne({
        where: {
          channel_id: channelId
        }
      })
      if (!settings) {
        settings = new ChatSettings();
        settings.fill({
          channel_id: channelId
        })
        await settings.save();
      }
      this.settings[channelId] = settings;
    }
    return this.settings[channelId];
  }

  getSockets(channelId: number): Array<any> {
      let sockets = [];

      if (this.users[channelId]) {
          this.users[channelId].forEach(user => {
              user.sockets.forEach(socket => {
                  sockets.push(socket);
              })
          })
      }
      if (this.guests[channelId]) {
          this.guests[channelId].forEach(socket => {
              sockets.push(socket);
          })
      }
      return sockets;
  }

  async addMessage(channel: Channel, user: ChatUserInfo, {content}: {content: string}) {
    const date = new Date();
    const timestamp = date.getTime();
    let messageInDb = new ChatMessage();
    messageInDb.fill({
      author_id: user.id,
      channel_id: channel.id,
      content,
      created_at: date
    })
    await messageInDb.save();
    let message = {
      id: messageInDb.id,
      timestamp,
      author: this.getVisibleUserInfo(user),
      content
    }
    this.messages[channel.id].push(message);
    this.sendUpdateToRoom(channel.id, 'NEW_MESSAGE', {message});
    return message;
  }

  async deleteMessage(channel: Channel, user: ChatUserInfo, {id}: {id: Number}) {
    const message = await this.getMessageById(id);
    const canEdit = await this.canEditMessage(message, channel, user);
    if (canEdit) {
      if (this.messages[channel.id]) {
        this.messages[channel.id] = this.messages[channel.id].filter(chatMessage => chatMessage.id !== message.id);
      }
      this.sendUpdateToRoom(channel.id, 'MESSAGE_DELETED', {id: message.id});
      await message.remove();
    }
  }

  async canEditMessage(message: ChatMessage, channel: Channel, user: ChatUserInfo) {
    if (!message || !message.author || message.channel_id !== channel.id) {
      return;
    }
    const author = await this.createUserInfoObject(channel, message.author);
    if (user.id === author.id) {
      return true;
    }
    if (user.isAdmin) {
      return true;
    }
    if (user.isModerator && !author.isModerator && !author.isAdmin) {
      return true;
    }
  }

  async getMessageById(id: Number): Promise<ChatMessage> {
    const message = await ChatMessage.findOne({
      where: {
        id
      },
      order: {
        created_at: 'ASC',
      },
      relations: ['author']
    });
    return message;
  }

  async getMessages(channel: Channel): Promise<Array<ChatMessageInfo>> {
    if (!this.messages[channel.id]) {
      const messages = await ChatMessage.find({
        where: {
          channel_id: channel.id
        },
        order: {
          created_at: 'ASC',
        },
        relations: ['author']
      });
      this.messages[channel.id] = [];
      for (const message of messages) {
        const author = await this.createUserInfoObject(channel, message.author);
         const messageInfo = {
          id: message.id,
          timestamp: message.created_at.getTime(),
          author: this.getVisibleUserInfo(author),
          content: message.content
        } as ChatMessageInfo;
        this.messages[channel.id].push(messageInfo);
      }
    }
    return this.messages[channel.id];
  }

  handleUpdate(channel: Channel, user: ChatUserInfo, {type, payload}: {type: ChatClientUpdateType, payload: any}) {
    switch (type) {
      case ChatClientUpdateType.SEND_MESSAGE:
        this.addMessage(channel, user, payload);
        break;
      case ChatClientUpdateType.DELETE_MESSAGE:
        this.deleteMessage(channel, user, payload);
        break;
      default:
        break;
    }
  }

}

let chatManager = new ChatManager();
export {chatManager};

