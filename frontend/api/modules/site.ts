import api from "../index";

const BASE_PATH = "site";

export const SiteGetFrontendConfig = async(): Promise<any> => {
  const res = await api.get(`${BASE_PATH}/frontend-config`);
  return res.data.config;
};

export const SiteGetFullConfig = async(): Promise<any> => {
  const res = await api.get(`${BASE_PATH}/config`);
  return res.data.config;
};

export const SiteSetConfig = async(config: any): Promise<boolean> => {
  const res = await api.post(`${BASE_PATH}/config`, config);
  return res.data.status;
};


