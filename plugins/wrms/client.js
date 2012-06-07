plugins.wrms = {

    html: null,

    start: function() {
        html = ('<div class="plugin" id="wrms"><h1>WRMS</h1><ol></ol></div>');
        $('div#body').append(html);
    },


    receiveData: function(data) {

        // Check for a single message (is not array?)
        if (!$.isArray(data)) {
            data = [data];
        } else {
        }

        for (c in data) {
            var content = data[c];

            var link = $('<a>').html(content.brief);
            link.attr('href', 'http://wrms.catalyst.net.nz/wr.php?request_id='+content.request_id);
            var status = content.status_desc;
            var node = $('<li>').append(link).append(' - ').append(status);
            node.hide();

            $('div#wrms ol').append(node);
            if ($('div#wrms ol li').length > 10) {
                $('div#wrms ol li:first').slideUp().remove();
            }

            node.slideDown();
        }
    }
}
