/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */
import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: (window.location.hostname == import.meta.env.VITE_REVERB_HOST)?import.meta.env.VITE_REVERB_HOST:window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

/** 
 * Testing Channels & Events & Connections
 */
window.Echo.private("App.Models.Account."+window["userId"]).notification((notification) => {
    if (notification.type == "App\\Notifications\\FeedbackSent") {
        new Audio('/audio/feedback-bell.mp3').play();
    } else if (notification.type == "App\\Notifications\\DocumentUpload") {
        new Audio('/audio/document-bell.mp3').play();
    }
    
    if (document.querySelector(".badge")) document.querySelector(".badge").innerText++;
    else document.querySelector(".fa.fa-envelope").insertAdjacentHTML('beforebegin', '<span class="badge">1</span> ');

    var notificationObject = '<li>';
    if (notification.type == "App\\Notifications\\FeedbackSent") {
        notificationObject += '<a href="/feedback/'+notification.id+'">';
    } else if (notification.type == "App\\Notifications\\DocumentUpload") {
        notificationObject += '<a href="/document/'+notification.id+'">';
    }
    
    notificationObject += '<div><strong>'+notification.employee_name+'</strong></div>';
    notificationObject += '<div>'+notification.title+'<br />';

    if (notification.type == "App\\Notifications\\FeedbackSent") {
        notificationObject += '<span class="label label-info">Feedback</span>';
    } else if (notification.type == "App\\Notifications\\DocumentUpload") {
        notificationObject += '<span class="label label-warning">Document</span>';
    }
    notificationObject += '</div></a></li><li class="divider"></li>';

    document.querySelector("a.text-center").parentElement.parentElement.firstElementChild.insertAdjacentHTML('beforebegin', notificationObject);
});

function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    var rawData = window.atob(base64);
    var outputArray = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function storePushSubscription(pushSubscription) {
    const token = document.querySelector('meta[name=csrf-token]').getAttribute('content');

    fetch('/push', {
        method: 'POST',
        body: JSON.stringify(pushSubscription),
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-Token': token
        }
    })
        .then((res) => {
            return res.json();
        })
        .then((res) => {
            console.log(res)
        })
        .catch((err) => {
            console.log(err)
        });
}

function subscribeUser() {
    navigator.serviceWorker.ready
        .then((registration) => {
            const subscribeOptions = {
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(window["publicKey"])
            };

            return registration.pushManager.subscribe(subscribeOptions);
        })
        .then((pushSubscription) => {
            console.log('Received PushSubscription: ', JSON.stringify(pushSubscription));
            storePushSubscription(pushSubscription);
        });
}

function initPush() {
    if (!navigator.serviceWorker.ready) {
        return;
    }

    new Promise(function (resolve, reject) {
        const permissionResult = Notification.requestPermission(function (result) {
            resolve(result);
        });

        if (permissionResult) {
            permissionResult.then(resolve, reject);
        }
    })
        .then((permissionResult) => {
            if (permissionResult !== 'granted') {
                throw new Error('We weren\'t granted permission.');
            }
            subscribeUser();
        });
}

function initSW() {
    if (!"serviceWorker" in navigator) {
        //service worker isn't supported
        return;
    }

    //don't use it here if you use service worker
    //for other stuff.
    if (!"PushManager" in window) {
        //push isn't supported
        return;
    }

    //register the service worker
    navigator.serviceWorker.register('/sw.js')
        .then(() => {
            console.log('serviceWorker installed!')
            initPush();
        })
        .catch((err) => {
            console.log(err)
        });
}

initSW();