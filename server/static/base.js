// Copyright 2009 FriendFeed
//
// Licensed under the Apache License, Version 2.0 (the "License"); you may
// not use this file except in compliance with the License. You may obtain
// a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
// WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
// License for the specific language governing permissions and limitations
// under the License.

var plugins = {};

$(document).ready(function() {
    if (!window.console) window.console = {};
    if (!window.console.log) window.console.log = function() {};

    // Set the name of the hidden property
    updater.config.hidden = false;
    var h = ['hidden', 'mozHidden', 'msHidden', 'webkitHidden'];
    for (var i = 0; i < h.length; i++) {
        if (typeof document[h[i]] !== "undefined") {
            updater.config.hidden = h[i];
            break;
        }
    }

    for (p in plugins) {
        plugin = plugins[p];
        console.log('Start plugin '+p);
        plugin.start();
    }

    updater.start();
});


// Websocket handler
var updater = {
    config: {},
    socket: null,

    start: function() {
        // Create websocket
        var url = "ws://" + location.host + "/chatsocket";
        if ("WebSocket" in window) {
            updater.socket = new WebSocket(url);
        } else {
            updater.socket = new MozWebSocket(url);
        }

        // On websocket message receive
        updater.socket.onmessage = function(event) {
            // Get data
            var data = JSON.parse(event.data);

            // Loop through messages
            updater.routeMessage(data);
        };

        // On websocket close
        updater.socket.onclose = function() {
            $('body').prepend($('<div class="error">Connection lost, please refresh browser</div>'));
        };
    },

    // Route received message
    routeMessage: function(data) {
        // Get data
        var plugin = $(data.html).data('plugin');
        var content = $(data.html).text();

        if (!plugins[plugin]) {
            return;
        }

        // Check if dashboard is not hidden, or if it is that it supports background data
        if (!document[updater.config.hidden] || updater.pluginSupportsBackgroundData(plugin)) {
            // Push to plugin
            console.log('Data received for '+plugin+' plugin');
            plugins[plugin].receiveData(jQuery.parseJSON(content));
        }
    },

    // Check plugin supports receiving background data
    pluginSupportsBackgroundData: function(plugin) {
        return typeof plugins[plugin].noBackgroundData === "undefined";
    }
};

var videoElement = document.getElementById("videoElement");
