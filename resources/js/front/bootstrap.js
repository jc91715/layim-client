window.Vue = require('vue');
window.Bus = new Vue();
window.Laravel = {};
import './plugins/axios'
import './plugins/element'

Laravel.router = require('./common/backend-router-generator')(require('./route.const'));
Laravel.frontRouter = require('./common/backend-router-generator')(require('./route.const'),'api.front.');
