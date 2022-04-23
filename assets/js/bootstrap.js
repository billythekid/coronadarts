window.axios = require('axios');

window._ = require('lodash');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.newsToken = process.env.MIX_AUTH_NEWS;
window.finderToken = process.env.MIX_AUTH_FINDER;
