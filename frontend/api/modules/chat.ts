import api from "../index";
import { ChatUpdateType, ChatUserInfo, ChatMessage } from '~/types/chat'

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


export const ChatSendMessage = (socket: WebSocket, message: ChatMessage): void => {
  sendUpdate(socket, 'SEND_MESSAGE', message);
};
