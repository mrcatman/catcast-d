import api from "../index";
import Paginator from '~/types/Paginator'
import User from '~/types/User'

const BASE_PATH = "users";

export const UsersSearch = async (query: string): Promise<Paginator<User>> => {
  const res = await api.get(`${BASE_PATH}/search?q=${query}`);
  return res.data as Paginator<User>;
};
