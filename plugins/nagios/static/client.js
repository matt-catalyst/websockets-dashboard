plugins.nagios = {

    html: null,

    start: function() {
        html = ('<div class="plugin" id="nagios"><h1>Server States</h1><div class="lastchange"></div></div>');
        $('div#body').append(html);
    },


    receiveData: function(data) {

        // Check for a single message (is not array?)
        if (!$.isArray(data)) {
            data = [data];
        }

        var container = $('div#nagios');
        var existing = $('li', container).clone();

        // Only care about the newest message
        data = data.pop();

        // Last change
        var lastchange = $('div.lastchange', container);
        if (data['lastchange']) {
            var c = new Date();
            c.setTime(data['lastchange'] * 1000);

            lastchange.html('Last change: '+c.toDateString()+' '+c.toTimeString());
        }

        for (c in data['groups']) {

            var title = c;
            var codes = data['groups'][c];

            var group = $('div#'+title, container);
            if (!group.length) {
                var group = $('<div id="'+title+'"><h2>'+title+'</h2><ul></ul></div>');
                $('h1', container).after(group);
            }

            var codelist = $('ul', group);
            codelist.html('');
            for (cd in codes) {
                var code = codes[cd];
                codelist.append($('<li>'+cd+': '+code+'</li>'));
            }

            // Set state
            $('h2', group).attr('class', data['states'][c]);
        }
    }
}
