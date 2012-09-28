plugins.messages = {

    html: null,

    start: function() {
        html = ('<div class="plugin" id="messages"><h1>Messages (<a target="_blank" href="http://192.168.2.245/messages.php">send your own!</a>)</h1><ol></ol></div>');
        $('div#body').append(html);
    },


    receiveData: function(data) {

        // Check for a single message (is not array?)
        if (!$.isArray(data)) {
            data = [data];
        }

        var messagebox = $('div#messages ol');

        for (c in data) {
            var content = $(data[c]);

            var node = $('<li>').append(content);
            node.hide();

            messagebox.append(node);
            if ($('li', messagebox).length > 10) {
                $('li:first', messagebox).slideUp().remove();
            }

            node.slideDown();
        }
    }
}
