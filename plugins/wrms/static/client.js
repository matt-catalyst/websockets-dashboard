plugins.wrms = {

    html: null,

    start: function() {
        html = ('<div class="plugin" id="wrms"><h1>WRMS</h1><div class="fader"></div><ol></ol></div>');
        $('div#body').append(html);
    },


    receiveData: function(data) {

        // Check for a single message (is not array?)
        if (!$.isArray(data)) {
            data = [data];
        }

        for (c in data) {
            var content = data[c];

            var link = $('<a>').html('<em>['+content.system_code+']</em> '+content.brief);
            link.attr('href', content.request_url);
            var status = content.status_desc+' [WR#'+content.request_id+']';
            var node = $('<li>').append(link).append(' - ').append(status);
            node.hide();

            $('div#wrms ol').prepend(node);
            if ($('div#wrms ol li').length > 20) {
                $('div#wrms ol li:last').slideUp().remove();
            }

            node.fadeIn(1000);
        }
    }
}
