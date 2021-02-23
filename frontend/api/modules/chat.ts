import api from "../index";

const BASE_PATH = "chat";

export const ChatGetData = async(id: number): Promise<any> => {
  const res = await api.get(`${BASE_PATH}/${id}`);
  return res.data.data;
};

