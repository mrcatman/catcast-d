import axios, { AxiosInstance, AxiosRequestConfig, AxiosResponse } from "axios";
import { FormErrors } from '~/types/forms'
import { notifyErrors } from '~/helpers/notifications'

const OnResponseSuccess = (response: AxiosResponse<any>): AxiosResponse<any> =>
  response;

const OnResponseFailure = (error: any): Promise<FormErrors | null> => {
  const httpStatus = error?.response?.status;
  const errors = error?.response?.data?.errors;
  if (errors) {
    return Promise.reject(errors as FormErrors);
  }
  const errorText = error?.response?.data?.text || error?.response?.data?.error;

  notifyErrors([errorText ? errorText : `Error ${httpStatus}`]);
  return Promise.reject(null);
};

const instance: Readonly<AxiosInstance> = axios.create({
  baseURL: localStorage.getItem('backend_url') ? localStorage.getItem('backend_url')! :  'http://localhost:4002/api/',
  timeout: 10000,
  withCredentials: true
});

instance.interceptors.response.use(OnResponseSuccess, OnResponseFailure);

export default instance;
