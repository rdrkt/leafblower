
var viewManager = (function () {

    var _this = this;

    _this.run = function () {

        _this.domFix();

        _this.loadEvents();

        return _this;
    };

    _this.domFix = function () {
        $('main').isotope();
    };

    _this.loadEvents = function () {

        $(document).on('click', '.block', function () {
            $(this).animate({
                'height': 300,
                'width': 300
            }, 100, function () {
                
                $(this).toggleClass('large-block');
            });
        });

    };

    return _this.run();

});

$(document).ready(function () {
    var view = new viewManager();
});