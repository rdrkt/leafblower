
var viewManager = (function () {

    var _this = this;

    _this.run = function () {

        _this.loadEvents();

        return _this;
    };

    _this.loadEvents = function () {

        $(document).on('click', '.block', function () {
            $(this).toggleClass('large-block');
        });

    };

    return _this.run();

});

$(document).ready(function () {
    var view = new viewManager();
});