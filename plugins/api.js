export default function (context, inject) {

  const API = {
    request({
       url,
       method,
       data = {},
       settings = {}
    }) {
      return new Promise((resolve, reject) => {
        let headers = {};
        if (settings.formData) {
          headers = {'Content-Type': 'multipart/form-data'};
        }
        const axiosSettings = settings.axios || {};
        context.$axios.request({
          data,
          method,
          url,
          headers,
          ...axiosSettings
        }).then(({data}) => {
           if (!settings.noAlerts) {
            if (data.message) {
              context.store.commit('NEW_ALERT', {
                status: 1,
                text: data.message
              });
            } else if (settings.notifyOnSuccess) {
              context.store.commit('NEW_ALERT', {
                status: 1,
                text: 'global.saved'
              });
            }
          }
          data._has_errors = false;
          resolve(data);
        }).catch(({response}) => {
          const data = response?.data || {};
          if (data.message && !settings.noAlerts) {
            context.store.commit('NEW_ALERT', {
              status: 0,
              text: data.message
            });
          }
          if (settings.onError) {
            if (typeof settings.onError === 'function') {
              resolve(settings.onError(data));
            } else {
              resolve(settings.onError);
            }
            return;
          }
          data._has_errors = true;
          data._error_status = response?.status || 500;

          reject(data);
        })
      })
    },
    get(url, settings = {}) {
      return this.request({
        url,
        method: 'GET',
        data: {},
        settings
      });
    },
    post(url, data = {}, settings = {}) {
      return this.request({
        url,
        method: 'POST',
        data,
        settings
      });
    },
    put(url, data = {}, settings = {}) {
      return this.request({
        url,
        method: 'PUT',
        data,
        settings
      });
    },
    delete(url, settings = {}) {
      return this.request({
        url,
        method: 'DELETE',
        settings
      });
    },
    defaultPaginator: {
      current_page: 1,
      data: [],
      from: 1,
      last_page: 1,
      links: [],
      per_page: 30,
      to: 1,
      total: 0
    }
  }

  inject('api', API)
  context.$api = API

}


