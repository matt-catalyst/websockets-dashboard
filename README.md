# Prerequisites

* Tornado (http://www.tornadoweb.org) is installed - this can by done via easy_install

# To run

## Server

Copy ```config-dist.ini``` to ```config.ini``` and make any necessary changes.
Simply run ```python server/server.py``` from the repository root directory to start the server.
Then visit http://127.0.0.1:8888/ in your browser to view.

## Plugins

This is done on a per-plugin basis as they all work differently.


# Plugin interfaces:

Plugins communicate with dashboard clients through the Tornado websocket server via a REST interface. They can be written in any language, and run on-demand, by cron or be a persistent daemon.


## Plugin -> Clients

Communication to Tornado from the plugin is via a POST request.

    URL: http://tornadoserver:port/update/{$plugname}
    POST Request
    POST data should be in the format:
      data={$jsonformatteddata}

If you wish to send multiple data parts at once, instead of sending multiple POST requests you can send them in a single format the Tornado server understands for caching purposes.

Use the POST data format:

    data={$jsonformatteddata1}&data={$jsonformatteddata2}&data={$jsonformatteddata3}

The server will cache the last 20 messages from a plugin, and when a new client connects the server will pushed all cached messages to it.


## Client Plugin Javascript

Plugin data should be handled and formatted via javascript, this is the basic skeleton:

    plugins.pluginname = {

        /**
        * This prevents the plugin frontend from receiving data when the dashboard
        * is hidden (e.g. a different tab is open). This property is optional.
        */
        noBackgroundData: true,

        /**
         * This function runs when the dashboard is first loaded for a client
         */
        start: function() {
            html = ('<div class="plugin" id="pluginname"><h1>Plugin's name</h1><ol></ol></div>');
            $('div#body').append(html);
        },


        /**
         * The plugin may not always receive one piece of data at once (especially when a client first connects),
         * so remember to check to see if the data is an array
         */
        receiveData: function(data) {

            // Check for a single message (is not array?)
            if (!$.isArray(data)) {
                // Make it an array for simpler processing
                data = [data];
            }

            var messagebox = $('div#pluginname ol');

            for (c in data) {
                var content = data[c];

                var node = $('<li>').text(content);
                node.hide();

                messagebox.append(node);
                if ($('li', messagebox).length > 10) {
                    $('li:first', messagebox).slideUp().remove();
                }

                node.slideDown();
            }
        }
    }


jQuery and d3.js are included on the page, and when a client first connects all plugin ```client.js``` files are automatically included from the subdirectories symbolically linked in ```enabled-plugins```. Plugins can include additional files via $.getScript() using the URL format ```http://tornadoserver:port/plugin/pluginname/staticfilename```.
