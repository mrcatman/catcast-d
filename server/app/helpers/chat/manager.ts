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
export enum ChatClientUpdateType {
  SEND_MESSAGE = 'SEND_MESSAGE',
}

function sendUpdate(socket: any, type: String, payload: any = null) {
  socket.send(JSON.stringify({
    type,
    payload
  }))
}

function sendUpdateToRoom(users: Array<ChatUserInfo>, type: String, payload: any = null) {
  users.forEach(user => {
    if (user.sockets) {
      user.sockets.forEach(socket => {
        socket.send(JSON.stringify({
          type,
          payload
        }))
      })
    }
  })
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
  messages = {} as MessagesList;

  addUser(channelId: number, user: User, socket: any) {
    let userInfo = createUserInfoObject(user);
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
    sendUpdate(socket, 'CONNECTED');
    sendUpdate(socket, 'MESSAGES_LIST', {messages: this.getMessages(channelId)});
    sendUpdate(socket, 'USERS_LIST', {users: this.getUsers(channelId)});

    socket.on('message', (data) => {
      data = JSON.parse(data);

      if (data && data.type) {
        this.handleUpdate(channelId, user, {type: data.type, payload: data.payload});
      }
    });
    socket.on('close', () => {

      this.deleteUser(channelId, user);
    });

    return userInfo;
  }

  deleteUser(channelId: number, user: User) {
    let userInfo = createUserInfoObject(user);
    this.users[channelId] = this.users[channelId].filter(user => user.id !== userInfo.id)
  }

  getUsers(channelId: number): Array<ChatUserInfo> {
    return this.users[channelId] ? this.users[channelId].map(user => {
        return {
            id: user.id,
            login: user.login,
            domain: user.domain,
            avatar: user.avatar
        }
    }) : [];
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
    sendUpdateToRoom(this.users[channelId], 'NEW_MESSAGE', message);
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

