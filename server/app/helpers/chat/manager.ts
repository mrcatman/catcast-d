import { User } from '../../models/User'
import { ChatUpdateType } from '../../../../frontend/types/chat'
export type ChatUserInfo = {
  id: number;
  login: string;
  domain?: string;
  avatar?: string;
  sockets?: Array<any>;
}

export type ChatMessage = {
  id: number,
  ts: number,
  author: ChatUserInfo,
  content: String,
}

export type UsersList = {
  [key: number]: Array<ChatUserInfo>;
}
export type MessagesList = {
  [key: number]: Array<ChatMessage>;
}

export type GuestsList = {
  [key: number]: Array<any>;
}
export enum ChatClientUpdateType {
  SEND_MESSAGE = 'SEND_MESSAGE',
}



function createUserInfoObject(user: User): ChatUserInfo {
  return {
    id: user.id,
    login: user.login,
    domain: user.domain,
    avatar: user.avatar ? user.avatar.full_url : null
  }
}

class ChatManager {
  users = {} as UsersList;
  guests = {} as GuestsList;
  messages = {} as MessagesList;

  sendUpdate(socket: any, type: String, payload: any = null) {
    socket.send(JSON.stringify({
      type,
      payload
    }))
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

  addUser(channelId: number, user: User | null, socket: any): ChatUserInfo | null {
    let userInfo;
    if (user) {
      userInfo = createUserInfoObject(user);
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
      this.sendUpdateToRoom(channelId, 'USER_JOINED', this.getVisibleUserInfo(userInfo));
    } else {
      if (!this.guests[channelId]) {
        this.guests[channelId] = [];
      }
      this.guests[channelId].push(socket);
    }

    this.sendUpdate(socket, 'CONNECTED');
    this.sendUpdate(socket, 'MESSAGES_LIST', {messages: this.getMessages(channelId)});

    let users = this.getUsers(channelId);
    this.sendUpdate(socket, 'USERS_LIST', {users});
    this.sendUpdatedMembersCount(socket, channelId);

    if (user) {
      socket.on('message', (data) => {
        data = JSON.parse(data);

        if (data && data.type) {
          this.handleUpdate(channelId, user, { type: data.type, payload: data.payload });
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
    this.sendUpdateToRoom(channelId, 'USER_LEFT', this.getVisibleUserInfo(userInfo));
  }

  deleteGuest(channelId: number, userSocket: any): void {
    this.guests[channelId] = this.guests[channelId].filter(socket => socket.id !== userSocket.id)
  }

  getVisibleUserInfo(user: ChatUserInfo): any {
    return {
      id: user.id,
      login: user.login,
      domain: user.domain,
      avatar: user.avatar
    };
  }

  getUsers(channelId: number): Array<ChatUserInfo> {
    return this.users[channelId] ? this.users[channelId].map(user => {
        return this.getVisibleUserInfo(user)
    }) : [];
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

  addMessage(channelId: number, user: User, {content}: {content: string}) {
    let id = 0;
    if (!this.messages[channelId]) {
      this.messages[channelId] = [];
    } else {
      id = Math.max.apply(Math, this.messages[channelId].map(message => message.id)) + 1;
    }
    let message = {
      id,
      ts: (new Date().getTime()),
      author: createUserInfoObject(user),
      content
    }
    this.messages[channelId].push(message);
    this.sendUpdateToRoom(channelId, 'NEW_MESSAGE', message);
    return message;
  }


  getMessages(channelId: number): Array<ChatMessage> {
    return this.messages[channelId] ? this.messages[channelId] : [];
  }

  handleUpdate(channelId: number, user: User, {type, payload}: {type: ChatClientUpdateType, payload: any}) {
    switch (type) {
      case ChatClientUpdateType.SEND_MESSAGE:
        this.addMessage(channelId, user, payload);
        break;
      default:
        break;
    }
  }

}

let chatManager = new ChatManager();
export {chatManager};

