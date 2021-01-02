import api from "../index";
import User from '~/types/User'

const BASE_PATH = "auth";


export const AuthLogin = async (login: string, password: string): Promise<User> => {
  const res = await api.post(`${BASE_PATH}/login`, {
    login,
    password
  });
  return res.data.user as User;
};

export const AuthLogout = async (): Promise<Boolean> => {
  await api.post(`${BASE_PATH}/logout`);
  return true;
};

export const AuthRegister = async (email: string, login: string, password: string): Promise<User> => {
  const res = await api.post(`${BASE_PATH}/register`, {
    email, login, password
  });
  return res.data.user as User;
};

export const AuthGetMe = async(): Promise<User | null> => {
  const res = await api.get(`${BASE_PATH}/me`);
  return res.data.user as User | null;
};

export const AuthUpdateProfile = async (data: Partial<User>): Promise<User> => {
  const res = await api.post(`${BASE_PATH}/update-profile`, data);
  return res.data.user as User;
};
