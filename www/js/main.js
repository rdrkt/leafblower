
var viewManager = (function () {

    var _this = this;
    _this.socket = {};

    _this.run = function () {

        _this.socket = io.connect('http://leafblower.rdrkt.com:8080');
        _this.loadEvents();
        _this.reshapePage();
        _this.blockController = new blockController();

        return _this;
    };

    _this.reshapePage = function () {
        $('main').isotope();
    };

    //check current device
    _this.getDeviceType = function () {
        if (window.matchMedia("only screen and (max-device-width: 721px) and (max-width:361px) and (-webkit-min-device-pixel-ratio:2) and (orientation: portrait), only screen and (max-device-height: 1281px) and (max-height:400px) and (-webkit-min-device-pixel-ratio:2) and (orientation: landscape),only screen and (max-width: 690px) and (-webkit-max-device-pixel-ratio:1), only screen and (max-device-width: 690px) and (-webkit-min-device-pixel-ratio: 1.5) and (orientation: portrait), only screen and (max-device-height: 690px) and (-webkit-min-device-pixel-ratio: 1.5) and (orientation: landscape), only screen and (max-width: 690px) and (max--mozdevice-pixel-ratio:1), only screen and (max-device-width: 690px) and (min--mozdevice-pixel-ratio: 1.5) and (orientation: portrait), only screen and (max-device-height: 690px) and (min--mozdevice-pixel-ratio: 1.5) and (orientation: landscape), only screen and (max-width: 690px) and (max-device-pixel-ratio:1), only screen and (max-device-width: 690px) and (min-device-pixel-ratio: 1.5) and (orientation: portrait), only screen and (max-device-height: 690px) and (min-device-pixel-ratio: 1.5) and (orientation: landscape), only screen and (max-width: 690px) and (max-resolution: 1dppx), only screen and (max-device-height: 960px) and (min-resolution: 1.5dppx) and (orientation: landscape), only screen and (max-device-width: 960px) and (min-resolution: 1.5dppx) and (orientation: portrait)").matches) {
            return 'mobile';
        } else if (window.matchMedia("only screen and (min-width:691px) and (max-width: 960px) and (-webkit-max-device-pixel-ratio:1), only screen and (min-device-height:691px) and (max-device-height: 960px) and (-webkit-min-device-pixel-ratio: 1.5) and (orientation: landscape), only screen and (min-device-width:691px) and (max-device-width: 960px) and (-webkit-min-device-pixel-ratio: 1.5) and (orientation: portrait), only screen and (min-width:691px) and (max-width: 960px) and (max--moz-device-pixel-ratio:1), only screen and (min-device-height:691px) and (max-device-height: 960px) and (min--moz-device-pixel-ratio: 1.5) and (orientation: landscape), only screen and (min-device-width:691px) and (max-device-width: 960px) and (min--mozdevice-pixel-ratio: 1.5) and (orientation: portrait), only screen and (min-width:691px) and (max-width: 960px) and (max-device-pixel-ratio:1), only screen and (min-device-height:691px) and (max-device-height: 960px) and (min-device-pixel-ratio: 1.5) and (orientation: landscape), only screen and (min-device-width:691px) and (max-device-width: 960px) and (min-device-pixel-ratio: 1.5) and (orientation: portrait), only screen and (min-width:691px) and (max-width: 960px) and (max-resolution: 1dppx), only screen and (min-device-height:691px) and (max-device-height: 960px) and (min-resolution: 1.5dppx) and (orientation: landscape), only screen and (min-device-width:691px) and (max-device-width: 960px) and (min-resolution: 1.5dppx) and (orientation: portrait)").matches) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    };

    _this.loadEvents = function () {

        $(document).on('click', '.block', function () {

            if ($(this).hasClass('large-block')) { var newWidth = 150, newHeight = 150; }
            else { var newWidth = 310, newHeight = 310; }

            $(this).animate({
                'height': newHeight,
                'width': newWidth
            }, 100, function () {
                $(this).toggleClass('large-block');
                _this.reshapePage();
            });
        });

        //run it on window resize
        $(window).on('resize', function () {
            _this.reshapePage();
        });

        //orientation change detect
        $(window).on('orientationchange', function () {
            _this.reshapePage();
        });

        _this.socket.on('connect', function () {
            _this.socket.emit('join room', document.location.hash.replace('#', ''));
        });

        _this.socket.on('data', function (data) {
            _this.blockController.handleData(data);
        });
    };

    return _this.run();

});

$(document).ready(function () {
    var view = new viewManager();
});