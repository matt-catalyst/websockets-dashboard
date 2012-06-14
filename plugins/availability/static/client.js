plugins.availability = {

    html: null,

    start: function() {
        html = ('<div class="plugin" id="availability"><h1>Availability</h1><ol></ol></div>');
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

            var title = $('<h2>').text(content.name);
            var image = $('<img>').attr('src', content.image).attr('height', 60).attr('width', 45);
            var li1 = $('<li><strong>A:</strong></li>').append(content.allocated);
            var li2 = $('<li><strong>P:</strong></li>').append(content.inprogress);
            var ul = $('<ul>').append(li1).append(li2);
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
