import api from "../index";
import UserPermissions from '~/types/UserPermissions'

const BASE_PATH = "permissions";

export const PermissionsGetList = async(): Promise<Array<String>> => {
  const res = await api.get(`${BASE_PATH}`);
  return res.data.permissions;
};

export const PermissionsGetMy = async(): Promise<Array<UserPermissions>> => {
  const res = await api.get(`${BASE_PATH}/my`);
  return res.data.permissions;
};
