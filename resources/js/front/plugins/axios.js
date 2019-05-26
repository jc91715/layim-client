"use strict";

import Vue from 'vue';
import axios from "axios";
import store from '@/store/index'
import { getCsrfToken } from '@/utils/auth'
// Full config:  https://github.com/axios/axios#request-config
// axios.defaults.baseURL =  process.env.baseURL || process.env.apiUrl || '';


let config = {
    baseURL: process.env.MIX_APP_URL || process.env.apiUrl || "http://blog.work",
    timeout: 60 * 1000, // Timeout
    withCredentials: true, // Check cross-site Access-Control
};

const _axios = axios.create(config);


_axios.interceptors.request.use(
    function(config) {
        // Do something before request is sent
        return config;
    },
    function(error) {
        // Do something with request error

        return Promise.reject(error);
    }
);
// Add a response interceptor
_axios.interceptors.response.use(
    function(response) {
        // Do something with response data
        return response;
    },
    function(error) {
        switch (error.response.status) {
            case 401:

                break;
            case 422:
                let data = error.response.data.errors
                let content = ''
                Object.keys(data).map(function (key) {
                    let value = data[key]
                    content += value[0]+'<br>'
                })
                break;
        }
        // Do something with response error
        return Promise.reject(error);
    }
);
Plugin.install = function(Vue, options) {
    Vue.axios = _axios;
    window.axios = _axios;
    Object.defineProperties(Vue.prototype, {
        axios: {
            get() {
                return _axios;
            }
        },
        $axios: {
            get() {
                return _axios;
            }
        },
    });
};

Vue.use(Plugin)

export default Plugin;
