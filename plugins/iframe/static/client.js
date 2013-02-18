plugins.iframe = {

    start: function() {
        html = ('<div class="plugin" id="iframe"><iframe id="iframe_frame" src="http://about:blank" width="100%" height="100%" frameborder="0"></iframe></div>');
        $('div#body').append(html);
    },


    receiveData: function(data) {
        $('#iframe_frame').attr('src', data[0]);
    },
}
