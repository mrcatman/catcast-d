import {UploadStatuses} from "@/helpers/uploads";
import {uuid} from "@/helpers/uuid";
const uploads = {
  namespaced: true,
  state: {
    list: [
      // {
      //   uuid: 1,
      //   id: 7,
      //   data: {
      //     title: 'my new big title my new big title my new big title',
      //     channel_id: 1,
      //     folder_id: -1
      //   },
      //   upload_status: UploadStatuses.STATUS_READY,
      //   upload_percent: 0,
      //   error: null,
      //   external: false
      // },
      // {
      //   uuid: 2,
      //   data: {
      //     title: 'lorem ipsum lorem lorem ipsum lorem ipsum ',
      //     channel_id: 1,
      //     folder_id: -1
      //   },
      //   upload_status: UploadStatuses.STATUS_UPLOADING,
      //   upload_percent: 50,
      //   error: null,
      //   external: false
      // },
      // {
      //   uuid: 3,
      //   data: {
      //     title: 'lorem ipsum lorem lorem ipsum lorem ipsum ',
      //     channel_id: 1,
      //     folder_id: -1
      //   },
      //   upload_status: UploadStatuses.STATUS_UPLOADING,
      //   upload_percent: 0,
      //   error: null,
      //   external: true
      // },
      // {
      //   uuid: 4,
      //   data: {
      //     title: 'lorem ipsum lorem lorem ipsum lorem ipsum ',
      //     channel_id: 1,
      //     folder_id: -1
      //   },
      //   upload_status: UploadStatuses.STATUS_UPLOADING,
      //   upload_percent: 50,
      //   error: 'test error',
      //   external: true
      // }
    ]
  },
  mutations: {
    addUpload(state, upload) {
      state.list.push({
        uuid: uuid(),
        ...upload,
        upload_status: UploadStatuses.STATUS_NOT_STARTED,
        upload_percent: 0,
        error: null,
      });
    },
    setList(state, list) {
      state.list = list;
    }
  },
};
export default uploads;
