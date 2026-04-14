import loadash from 'lodash'
import dataTables from 'datatables.net-bs4';

window._ = loadash

import * as Popper from '@popperjs/core'
window.Popper = Popper

import 'bootstrap'

import $ from 'jquery';
window.$ = $;

import axios from 'axios'
window.axios = axios

window.dt = dataTables;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import select2 from 'select2';

select2();


