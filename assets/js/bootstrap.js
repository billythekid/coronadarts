import axios from 'axios';
import lodash from 'lodash';

window.axios = axios;
window.lodash = lodash;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
