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

$(document).ready(function() {
    if (!window.console) window.console = {};
    if (!window.console.log) window.console.log = function() {};

    for (p in plugins) {
        plugin = plugins[p];
        console.log('Start plugin '+p);
        plugin.start();
    }

    updater.start();
});


// Websocket handler
var updater = {
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
        }
    },

    // Route received message
    routeMessage: function(data) {
        // Get data
        var plugin = $(data.html).data('plugin');
        var content = $(data.html).text();

        // Push to plugin
        console.log('data received for '+plugin+' plugin');
        if (plugins[plugin]) {
            plugins[plugin].receiveData(jQuery.parseJSON(content));
        }
    }
};

var plugins = {}
