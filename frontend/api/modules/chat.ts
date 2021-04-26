import api from "../index";
import { ChatUpdateType, ChatUserInfo, ChatMessage, ChatSettings } from '~/types/chat'

function sendUpdate(socket: WebSocket, type: String, payload: any = null) {
  socket.send(JSON.stringify({
    type,
    payload
  }))
}

const BASE_PATH = "chat";

export const ChatConnect = async(id: number): Promise<string> => {
  const res = await api.get(`${BASE_PATH}/${id}/connect`);
  return res.data.data.connect_key;
};

export const ChatSetColor = (socket: WebSocket, color: string): void => {
  sendUpdate(socket, 'SET_COLOR', color);
};

export const ChatSendMessage = (socket: WebSocket, message: Partial<ChatMessage>): void => {
  sendUpdate(socket, 'SEND_MESSAGE', message);
};

export const ChatDeleteMessage = (socket: WebSocket, message: Partial<ChatMessage>): void => {
  sendUpdate(socket, 'DELETE_MESSAGE', message);
};

export const ChatUpdateSettings = (socket: WebSocket, settings: Partial<ChatSettings>): void => {
  sendUpdate(socket, 'UPDATE_SETTINGS', settings);
};

export const ChatClear = (socket: WebSocket): void => {
  sendUpdate(socket, 'CLEAR_CHAT');
};

export const ChatBanUser = (socket: WebSocket, userId: number): void => {
  sendUpdate(socket, 'BAN_USER', { userId });
};


export const ChatUnbanUser = (socket: WebSocket, userId: number): void => {
  sendUpdate(socket, 'UNBAN_USER', { userId });
};
