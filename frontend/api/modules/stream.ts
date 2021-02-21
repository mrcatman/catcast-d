import api from "../index";
import StreamKey from '~/types/StreamKey'

const BASE_PATH = "stream";

export const StreamGetKey = async (channelId: number, forceGetNew: boolean = false): Promise<StreamKey> => {
  const res = await api.get(`${BASE_PATH}/key/${channelId}${forceGetNew ? '?new=1' : ''}`);
  return res.data.key as StreamKey;
};

export const StreamGetServers = async (): Promise<Array<String>> => {
  const res = await api.get(`${BASE_PATH}/servers`);
  return res.data.servers as Array<String>;
};

