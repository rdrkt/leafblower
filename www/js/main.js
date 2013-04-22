
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

        //run it on window resize
        $(window).on('resize', function () {
            _this.reshapePage();
        });

        //orientation change detect
        $(window).on('orientationchange', function () {
            _this.reshapePage();
        });

        //on connect, join profile "room"
        _this.socket.on('connect', function () {
            _this.socket.emit('join', document.location.hash.replace('#', ''));
        });

        //feedback that a profile room is joined
        _this.socket.on('joined', function (room) {
            var date = new Date()
            console.log('Joined ' + room + ' [' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + ' ' + (date.getDate() + 1) + '/' + date.getMonth() + '/' + date.getFullYear() + ']');
        });

        //feedback that connection is lost
        _this.socket.on('disconnect', function () {
            var date = new Date()
            console.log('socket failure, lost connection [' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds() + ' ' + (date.getDate() + 1) + '/' + date.getMonth() + '/' + date.getFullYear() + ']');
        });

        //data throughput from Node
        _this.socket.on('data', function (data) {
            _this.blockController.handleData(data);
            //$('footer').after('<p class="log-block" style="margin:0 auto; width:960px; font-family:monospace;">' + data.substr(0, 116) + ' ...</p>');
            //$('.log-block:nth(10)').remove();
            if ($('#log-counter').length < 1) {
                $('footer').after('<p id="log-counter">0</p>');
            } else {
                $('#log-counter').text(parseInt($('#log-counter').text())+1);
            }
        });

        //block delete event
        _this.socket.on('blockDelete', function (blockId) {
            _this.blockController.handleDelete(blockId);
        });

        //profile delete event - send back to list page
        _this.socket.on('profileDelete', function (d) {
            alert('The profile you are currently viewing has been removed, you will now be redirected to the profile listing page');
            document.location.href = 'list.php';
        });

        /*
        *
        *   - Actual DOM events
        *
        */


        //handles an expandable block being toggled to a full-width expandable block
        $(document).on('click', '.expandable-block .expander-link', function (e) {
            e.preventDefault();

            var $block = $(this).parents('.expandable-block');

            if ($block.hasClass('large-block')) { var newWidth = 150, newHeight = 150; }
            else { var newWidth = $('main').width() - 10, newHeight = 310; }

            //toggle large block class
            $block.toggleClass('large-block');

            $block.animate({
                'height': newHeight,
                'width': newWidth
            }, 500, function () {

                if ($block.is('.large-block')) {
                    $block.find('.large-only').fadeIn(250);
                    $('html, body').animate({ 'scrollTop': $block.offset().top }, 200);
                } else {
                    $block.find('.large-only').removeAttr('style');
                }
            });
        });

        //handles an expandable block being toggled to a 310x310 block
        $(document).on('click', '.growing-block .expander-link', function (e) {

            e.preventDefault();

            var $block = $(this).parents('.growing-block');

            if ($block.is('.large-block')) {

                $block.find('.large-only').fadeOut(250, function () {
                    $block.find('.large-only').removeAttr('style');
                    $block.animate({
                        'height': 150,
                        'width': 150
                    }, 500, function () {
                        //toggle large block class
                        $block.removeClass('large-block');
                    });
                });

            } else {

                //add large-block class
                $block.addClass('large-block');

                $block.animate({
                    'height': 310,
                    'width': 310
                }, 500, function () {
                    $block.find('.large-only').fadeIn(250);
                    $('html, body').animate({ 'scrollTop': $block.offset().top }, 200);
                });

            }

        });

    };

    return _this.run();

});

$(document).ready(function () {
    var view = new viewManager();
});