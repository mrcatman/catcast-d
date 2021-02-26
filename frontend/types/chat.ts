export enum ChatUpdateType {
  CONNECTED = 'CONNECTED',
  MESSAGES_LIST = 'MESSAGES_LIST',
  USERS_LIST = 'USERS_LIST',
  NEW_MESSAGE = 'NEW_MESSAGE'
}
export type ChatUserInfo = {
  id: number;
  login: string;
  domain?: string;
  avatar?: string;
}

export type ChatMessage = {
  id: number,
  ts: number,
  author: ChatUserInfo,
  content: String,
}
