
var mongoSlowQuery = (function () {

    var _this = this;

    _this.run = function () {
        //some markup? Yeh, g'wan.
        if ($('#mongoSlowQuery').length < 1) { $('main').append('<div class="block" id="mongoSlowQuery"><h3 class="expander-title"></h3><div class="large-only"></div></div>'); }
        return _this;
    };

    _this.setData = function (data) {
        $('#mongoSlowQuery').find('h3').attr('title', data.op + 'last slow query on "' + data.ns + '"').html(data.op + '<span>last slow query on "' + data.ns + '"</span>');
    };

    _this.deleteBlock = function () {
        $('#mongoSlowQuery').remove();
    };

    return _this.run();

});