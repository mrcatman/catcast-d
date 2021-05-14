import api from "../index";
import User from '~/types/User'
import Paginator from '~/types/Paginator'
import Channel from '~/types/Channel'

const BASE_PATH = 'admin';

export const AdminGetUsers = async({page, search = ''}: {page: number, search: string}, type: string): Promise<Paginator<User>> => {
  const res = await api.get(`${BASE_PATH}/users?type=${type}&page=${page}${search ? '&search=' + search : ''}`);
  return res.data.users as Paginator<User>;
};

export const AdminGetUsersAll = async({page, search = ''}: {page: number, search: string}): Promise<Paginator<User>> => {
  return AdminGetUsers({ page, search }, 'all');
};
export const AdminGetUsersLocal = async({page, search = ''}: {page: number, search: string}): Promise<Paginator<User>> => {
  return AdminGetUsers({ page, search }, 'local');
};
export const AdminGetUsersRemote = async({page, search = ''}: {page: number, search: string}): Promise<Paginator<User>> => {
  return AdminGetUsers({ page, search }, 'remote');
};

export const AdminUpdateUser = async(id: number, user: Partial<User>): Promise<User> => {
  const res = await api.post(`${BASE_PATH}/users/${id}`, user);
  return res.data.user as User;
};

export const AdminGetChannels = async({page, search = ''}: {page: number, search: string}): Promise<Paginator<Channel>> => {
  const res = await api.get(`${BASE_PATH}/channels?page=${page}${search ? '&search=' + search : ''}`);
  return res.data.channels as Paginator<Channel>;
};

