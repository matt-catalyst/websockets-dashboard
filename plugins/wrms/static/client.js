plugins.wrms = {

    html: null,

    start: function() {
        html = ('<div class="plugin" id="wrms"><h1>Incoming WRMS</h1><div class="fader"></div><ol></ol></div>');
        $('div#body').append(html);
    },


    receiveData: function(data) {

        // Check for a single message (is not array?)
        if (!$.isArray(data)) {
            data = [data];
        }

        var container = $('div#wrms ol');
        var existing = $('li', container).clone();

        for (c in data) {
            var content = data[c];

            if (content == 1) {
                // Ignore (this is a placeholder)
                continue;
            }

            var id = 'wr-'+content.request_id;

            var d = new Date();
            d.setTime(content.last_activity_epoch * 1000);

            var c = '';
            if (content.ranking > 10000) {
                c = 'urgent';
            }

            var find = $('li#'+id, container);

            var link = $('<a>').html('<em>['+content.organisation_code+']</em> '+content.brief);
            link.attr('href', content.request_url);
            var status = content.status_desc+' [WR#'+content.request_id+'] Last changed: '+d.toDateString();
            var node = $('<li>').attr('id', id).attr('class', c).data('date', content.last_activity_epoch);
            node.append(link).append(' - ').append(status);

            node.hide();

            if ($('li#'+id, container).length) {
                $('li#'+id, container).slideUp().remove();
            }

            container.prepend(node);
            if ($('li', container).length > 20) {
                $('li:last', container).slideUp().remove();
            }

            node.fadeIn(1000);

            // Remove from existing array
            existing = existing.filter('#'+id).remove();
        }

        if (existing.length) {
            existing.each(function() {
                $('#'+$(this).attr('id'), container).slideUp().remove();
            });
        }
    }
}
