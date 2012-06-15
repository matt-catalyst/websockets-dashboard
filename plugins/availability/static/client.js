plugins.availability = {

    html: null,

    start: function() {
        html = ('<div class="plugin" id="availability"><h1>Availability (A=allocated, P=inprogress, T=hoursthisweek, L=hourslastweek)</h1><ol></ol></div>');
        $('div#body').append(html);
    },


    receiveData: function(data) {

        // Check for a single message (is not array?)
        if (!$.isArray(data)) {
            data = [data];
        }

        var ol = $('div#availability ol');

        for (c in data) {
            var content = data[c];
            var shamedthisweek = content.shamedthisweek ? ' class=shamedthisweek' : '';
            var shamedlastweek = content.shamedlastweek ? ' class=shamedlastweek' : '';

            var title = $('<h2>').text(content.name);
            var image = $('<img>').attr('src', content.image).attr('height', 60).attr('width', 45);
            var li1 = $('<li><strong>A:</strong></li>').append(content.allocated);
            var li2 = $('<li><strong>P:</strong></li>').append(content.inprogress);
            var li3 = $('<li><strong>T:</strong></li>').append($('<span'+shamedthisweek+'>').text(content.hoursthisweek));
            var li4 = $('<li><strong>L:</strong></li>').append($('<span'+shamedlastweek+'>').text(content.hourslastweek));
            var ul = $('<ul>').append(li1).append(li2).append(li3).append(li4);
            var node = $('<li>').attr('id', content.id);
            node.append(title).append(image).append(ul);

            var oldnode = $('li#'+content.id, ol);
            if (oldnode.length) {
                oldnode.remove();
            }

            ol.append(node);
        }
    }
}
