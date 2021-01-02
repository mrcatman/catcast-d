import api from "../index";
import Channel from '~/types/Channel'

const BASE_PATH = "channels";

export const ChannelCreate = async (data: Partial<Channel>): Promise<Channel> => {
  const res = await api.post(`${BASE_PATH}`, data);
  return res.data.channel as Channel;
};

export const ChannelUpdate = async (data: Partial<Channel>): Promise<Channel> => {
  const res = await api.put(`${BASE_PATH}/${data.id}`, data);
  return res.data.channel as Channel;
};

export const ChannelsGetList = async (): Promise<Array<Channel>> => {
  const res = await api.get(`${BASE_PATH}`);
  return res.data.channels as Array<Channel>;
};

export const ChannelsGetListMy = async (): Promise<Array<Channel>> => {
  const res = await api.get(`${BASE_PATH}/my`);
  return res.data.channels as Array<Channel>;
};

export const ChannelGetById = async (id: number): Promise<Channel> => {
  const res = await api.get(`${BASE_PATH}/${id}`);
  return res.data.channel as Channel;
};

export const ChannelGetByUrl = async (url: string): Promise<Channel> => {
  const res = await api.get(`${BASE_PATH}/get-by-url/${url}`);
  return res.data.channel as Channel;
};


export const ChannelGetRights = async (id: number): Promise<Array<string>> => {
  const res = await api.get(`${BASE_PATH}/${id}/rights`);
  return res.data.rights as Array<string>;
};
