
window._ = require('lodash');

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    //require('bootstrap-sass');
} catch (e) {}

window.moment = require('moment');
require('moment-timezone');

window.Highcharts = require('highcharts');  
// Load module after Highcharts is loaded
require('highcharts/modules/exporting')(Highcharts);  

//import all the 3rd party libraries
window.Ladda = require('ladda');
require('icheck/icheck.min.js');
window.PerfectScrollbar = require('perfect-scrollbar').default;
window.screenfull = require('screenfull');

import jkanban from 'jkanban/dist/jkanban.min.js';
import tinymce from 'tinymce/tinymce';
import 'tinymce/themes/silver';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/link';
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/image';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/print';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/hr';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/pagebreak';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/visualchars';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/nonbreaking';
import 'tinymce/plugins/table';
import 'tinymce/plugins/template';
import 'tinymce/plugins/help';

window.PatternLock = require('patternlock/dist/patternlock.min.js');

window.Tagify = require('@yaireo/tagify/dist/tagify.min.js');
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo'

window.Pusher = require('pusher-js');

//if pusher enabled initialize push notification
if (typeof APP != 'undefined' && APP.PUSHER_ENABLED) {

    window.Echo = new Echo({
    	authEndpoint: base_path + '/broadcasting/auth',
        broadcaster: 'pusher',
        key: APP.PUSHER_APP_KEY,
        cluster: APP.PUSHER_APP_CLUSTER,
      	forceTLS: true
    });

    //if notification permission is not granted then request for permission
    if (Notification.permission !== 'denied' || Notification.permission === "default") {
    	Notification.requestPermission();
    }

    window.Echo.private('App.User.' + APP.USER_ID)
    	.notification((notification) => {
    	//if permission is granted then notify user
        if (Notification.permission === 'granted') {

        	//specify any additional options like: icon,image
        	var options = {
    		    body: notification.body
    		  };

            //notification title, link is optional but body is mandatory
            if (_.isUndefined(notification.title)) {
                notification.title = '';
            }

    		//create notification
            var notification_obj = new Notification(notification.title, options);

            //if action defined & clicked take user to that link
            if (!_.isUndefined(notification.link)) {
                notification_obj.onclick = function() {
                   window.open(notification.link);
                };
            }
        }
    });
}