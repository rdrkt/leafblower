
var adminCp = (function () {

    var _this = this;

    _this.run = function () {

        _this.fixDom();

        _this.loadEvents();

        return _this;
    };

    _this.fixDom = function () {
        var height = parseInt($('body').height() - $('header').outerHeight(true) - $('footer').outerHeight(true));
        $('aside').css('height', height + 'px');
    };

    _this.loadEvents = function () {

        $(document).on('click', 'header nav a', function () {
            e.preventDefault();
            $newactive = $($(this).attr('href'));
            $('section.active').slideUp(200, function () {
                $newactive.slideDown(200, function () {
                    $(this).addClasS('active');
                });
            });
        });

    };

    return _this.run();

});

$(document).ready(function () {
    var controlPanel = new adminCp();
});