import api from "../index";
import Picture from '~/types/Picture'

const BASE_PATH = "upload";

export const UploadPicture = async (picture: File): Promise<Picture> => {
  let fd = new FormData();
  fd.append('picture', picture);
  const res = await api.post(`${BASE_PATH}/pictures`, fd, {headers: {'Content-Type': 'multipart/form-data'}});
  return res.data.picture as Picture;
};
