
var mongoSlowQuery = (function () {

    var _this = this;

    _this.run = function () {
        //some markup? Yeh, g'wan.
        if ($('#mongoSlowQuery').length < 1) { $('main').append('<div class="block" id="mongoSlowQuery"><h3 class="expander-title"></h3><div class="large-only"></div></div>'); }
        return _this;
    };

    _this.setData = function (data) {
        delay = data.millis; //round to 1 decimal after by dividing by 1000 to get ms
        $('#mongoSlowQuery').find('h3').attr('title', delay  + 'ms on ' + data.ns).html(delay + '<span>ms<br />' + data.ns + '</span>');
    };

    _this.deleteBlock = function () {
        $('#mongoSlowQuery').remove();
    };

    return _this.run();

});