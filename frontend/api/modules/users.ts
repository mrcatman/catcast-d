import api from "../index";
import Paginator from '~/types/Paginator'
import User from '~/types/User'

const BASE_PATH = "users";

export const UsersSearch = async (query: string): Promise<Paginator<User>> => {
  const res = await api.get(`${BASE_PATH}/search?q=${query}`);
  return res.data as Paginator<User>;
};

export const UserGet = async (handle: string): Promise<User> => {
  const res = await api.get(`${BASE_PATH}/${handle}`);
  return res.data.user as User;
};

export const UserGetTeams = async (id: number): Promise<any> => {
  const res = await api.get(`${BASE_PATH}/${id}/teams`);
  return res.data.teams;
};

export const UserSubscribe = async (id: number): Promise<boolean> => {
  const res = await api.post(`${BASE_PATH}/${id}/subscribe`);
  return res.data.status as boolean;
};

export const UserUnsubscribe = async (id: number): Promise<boolean> => {
  const res = await api.post(`${BASE_PATH}/${id}/unsubscribe`);
  return res.data.status as boolean;
};

interface GetSubscribersResponse {
  subscribers_count: number,
  is_subscribed: boolean
}
export const UserGetSubscribersCount = async (id: number): Promise<GetSubscribersResponse> => {
  const res = await api.get(`${BASE_PATH}/${id}/subscribers-count`);
  return res.data as GetSubscribersResponse;
};
