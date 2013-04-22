
var mongoServerStats = (function () {

    var _this = this;

    _this.run = function () {
        //some markup? Yeh, g'wan.
        if ($('#mongoServerStats').length < 1) { $('main').append('<div class="block growing-block" id="mongoServerStats"><h3 class="expander-title"><a class="expander-link" href="" title=""></a></h3><div class="large-only"></div></div>'); }
        return _this;
    };

    _this.setData = function (data) {
        
        $('#mongoServerStats').find('h3').find('a').attr('title', data.connections.current + 'Connections on "' + data.host + '"').html(data.connections.current + '<span>Connections on "' + data.host + '"</span>');

        //get the large content block and empty, then inject content
        var $largeContent = $('#mongoServerStats').find('.large-only');
        $largeContent.empty();

        //connections display
        var html = '<div class="data-group"><h5>Connections</h5>';
        $.each(data.connections, function (key, value) {
            if (key != 'current') {
                html += '<p class="text-data"><span>' + key + '</span>' + value + '</p>';
            }
        });
        html += '</div>';
        $largeContent.append(html);

        //background flushing
        html = '<div class="data-group"><h5>Background flushing</h5>';
        $.each(data.backgroundFlushing, function (key, value) {
            if (key != 'last_finished') {
                html += '<p class="text-data"><span>' + key + '</span>' + value + '</p>';
            } else {
                value = parseInt(value.sec);
                var days = Math.floor(value / 60 / 60 / 24);
                value = value = (value / 60 / 60 / 24);
                var hrs = Math.floor(value/60/60);
                value = value - (hrs * 60*60);
                var mins = Math.floor(value/60);
                value = value - (mins * 60);
                var secs = Math.round(value);
                html += '<p class="text-data"><span>' + key + '</span>' + days + ' days ' + hrs + ' hrs ' + mins + ' mins</p>';
            }
        });
        html += '</div>';
        $largeContent.append(html);

    };

    _this.deleteBlock = function () {
        $('#mongoServerStats').remove();
    };

    return _this.run();

});