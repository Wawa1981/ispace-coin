import _ from 'lodash';
window._ = _;

import * as Popper from '@popperjs/core';
window.Popper = Popper;

import 'bootstrap'; // Si vous utilisez Bootstrap CSS/JS

/**
 * We'll load the axios HTTP library which provides a simple, convenient,
 * API for sending HTTP requests in your JavaScript applications. By
 * default, we this will include a CSRF token automatically. You may
 * customize this by editing the .env file.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events broadcast by Laravel. Echo and event broadcasting is
 * typically not required for most applications, but it's a great way
 * to build robust, real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     wsHost: import.meta.env.VITE_PUSHER_HOST ?? `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
