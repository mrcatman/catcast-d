import api from "../index";
import UserPermissions from '~/types/UserPermissions'

const BASE_PATH = "permissions";

interface PermissionsResponse {
  permissions: Array<string>,
  remote: Array<string>,
}
export const PermissionsGetList = async(): Promise<PermissionsResponse> => {
  const res = await api.get(`${BASE_PATH}`);
  return res.data as PermissionsResponse;
};


export const PermissionsGetMy = async(): Promise<UserPermissions[]> => {
  const res = await api.get(`${BASE_PATH}/my`);
  return res.data.permissions;
};

export const PermissionsConfirm = async(id: number): Promise<Boolean> => {
  await api.post(`${BASE_PATH}/${id}/confirm`);
  return true;
};

export const PermissionsReject = async(id: number): Promise<Boolean> => {
  await api.post(`${BASE_PATH}/${id}/reject`);
  return true;
};
