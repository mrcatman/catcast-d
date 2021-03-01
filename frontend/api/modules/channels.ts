import api from "../index";
import Channel from '~/types/Channel'
import Paginator from '~/types/Paginator'
import { UserChannelPermissions } from '~/helpers/permissions'
import Stream from '~/types/Stream'
import User from '~/types/User'
import UserPermissions from '~/types/UserPermissions'

const BASE_PATH = "channels";

export const ChannelCreate = async (data: Partial<Channel>): Promise<Channel> => {
  const res = await api.post(`${BASE_PATH}`, data);
  return res.data.channel as Channel;
};

export const ChannelUpdate = async (data: Partial<Channel>): Promise<Channel> => {
  const res = await api.put(`${BASE_PATH}/${data.id}`, data);
  return res.data.channel as Channel;
};

export const ChannelsGet = async (page: number = 1): Promise<Paginator<Channel>> => {
  const res = await api.get(`${BASE_PATH}?page=${page}`);
  return res.data as Paginator<Channel>;
};

export const ChannelsGetOnline = async (page: number = 1): Promise<Paginator<Channel>> => {
  const res = await api.get(`${BASE_PATH}/online?page=${page}`);
  return res.data as Paginator<Channel>;
};

export const ChannelsGetLocal = async (page: number = 1): Promise<Paginator<Channel>> => {
  const res = await api.get(`${BASE_PATH}/local?page=${page}`);
  return res.data as Paginator<Channel>;
};

export const ChannelsGetListMy = async (): Promise<Paginator<Channel>> => {
  const res = await api.get(`${BASE_PATH}/my`);
  return res.data as Paginator<Channel>;
};

export const ChannelsSearch = async (query: string): Promise<Paginator<Channel>> => {
  const res = await api.get(`${BASE_PATH}/search?q=${query}`);
  return res.data as Paginator<Channel>;
};

export const ChannelGetById = async (id: number): Promise<Channel> => {
  const res = await api.get(`${BASE_PATH}/${id}`);
  return res.data.channel as Channel;
};

export const ChannelGetByUrl = async (url: string): Promise<Channel> => {
  const res = await api.get(`${BASE_PATH}/get-by-url/${url}`);
  return res.data.channel as Channel;
};

export const ChannelGetPermissions = async (id: number): Promise<Array<string>> => {
  const res = await api.get(`${BASE_PATH}/${id}/permissions`);
  return res.data.permissions as Array<UserChannelPermissions>;
};

export const ChannelSubscribe = async (id: number): Promise<boolean> => {
  const res = await api.post(`${BASE_PATH}/${id}/subscribe`);
  return res.data.status as boolean;
};

export const ChannelUnsubscribe = async (id: number): Promise<boolean> => {
  const res = await api.post(`${BASE_PATH}/${id}/unsubscribe`);
  return res.data.status as boolean;
};

interface GetSubscribersResponse {
  subscribers_count: number,
  is_subscribed: boolean
}
export const ChannelGetSubscribersCount = async (id: number): Promise<GetSubscribersResponse> => {
  const res = await api.get(`${BASE_PATH}/${id}/subscribers-count`);
  return res.data as GetSubscribersResponse;
};

export const ChannelSetStreamSettings = async (id: number, data: any): Promise<any> => {
  await api.put(`${BASE_PATH}/${id}/stream-settings`, data);
  return true;
};

export const ChannelGetStreams = async (id: number, page: number): Promise<Paginator<Stream>> => {
  const res = await api.get(`${BASE_PATH}/${id}/streams?page=${page}`);
  return res.data.streams as Paginator<Stream>;
};

interface GetTeamResponse {
  owner: User,
  team: Array<any>
}

export const ChannelGetTeam = async (id: number): Promise<GetTeamResponse> => {
  const res = await api.get(`${BASE_PATH}/${id}/team`);
  return res.data as GetTeamResponse;
};

export const ChannelAddUserToTeam = async (id: number, data: any): Promise<UserPermissions> => {
  const res = await api.post(`${BASE_PATH}/${id}/team`, data);
  return res.data as UserPermissions;
};
export const ChannelRemoveUserFromTeam = async (id: number, permissions_id: number): Promise<boolean> => {
  await api.delete(`${BASE_PATH}/${id}/team/${permissions_id}`);
  return true;
};
