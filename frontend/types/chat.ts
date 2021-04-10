export enum ChatUpdateType {
  CONNECTED = 'CONNECTED',
  MESSAGES_LIST = 'MESSAGES_LIST',
  USERS_LIST = 'USERS_LIST',
  NEW_MESSAGE = 'NEW_MESSAGE',
  USER_JOINED = 'USER_JOINED',
  USER_LEFT = 'USER_LEFT',
  MEMBERS_COUNT = 'MEMBERS_COUNT',
  MESSAGE_DELETED = 'MESSAGE_DELETED',
  CHAT_SETTINGS = 'CHAT_SETTINGS'
}
export type ChatUserInfo = {
  id: number;
  login: string;
  domain?: string;
  avatar?: string;
  isAdmin?: boolean;
  isModerator?: boolean;
  webUrl?: boolean;
}

export type ChatMessage = {
  id: number,
  timestamp: number,
  author: ChatUserInfo,
  content: string,
}

export type ChatSettings = {
  disabled: boolean,
  motd: string
}
