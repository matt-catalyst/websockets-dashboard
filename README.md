Plugin interfaces:

Plugins communicate with dashboard clients through the Tornado websocket server via a REST interface. They can be written in any language, and run on-demand, by cron or be a persistent daemon.


Plugin -> Clients

Communication to Tornado from the plugin is via a POST request.

    URL: http://tornadoserver:port/update/{$plugname}
    POST Request
    POST data should be in the format:
      data={$jsonformatteddata}

If you wish to send multiple data parts at once, instead of sending multiple POST requests you can send them in a signle format the Tornado server understands for caching purposes.

Use the POST data format:

    data={$jsonformatteddata1}&data={$jsonformatteddata2}&data={$jsonformatteddata3}



Client Plugin Javascript

Plugin data should be handled and formatted via javascript, this is the basic skeleton:

    plugins.pluginname = {

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


jQuery is included on the page, and when a client first connects all plugin client.js files are automatically included from the plugins/ subdirectories.
