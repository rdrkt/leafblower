
var countingMongodb = (function () {

    var _this = this;

    _this.run = function () {
        _this.loadEvents();

        return _this;
    };

    _this.setData = function (data) {

        var newTotal = 0;

        $('#countingMongodb').find('.large-only').empty();

        $.each(data, function (key, item) {
            newTotal += item.count;
            $('#countingMongodb').find('.large-only').append('<p class="info-blob collection"><span>' + item['_id'] + '</span>[' + item.count + ']</p>');
        });

        $('#countingMongodb').find('h3').find('a').attr('title', newTotal + ' Documents').html(newTotal + '<span>documents</span>');
    };

    _this.deleteBlock = function () {
        $('#countingMongodb').remove();
    };

    _this.loadEvents = function () {
        if ($('#countingMongodb').length < 1) { $('main').append('<div class="block" id="countingMongodb"><h3><a href="" title=""></a></h3><div class="large-only"></div></div>'); }

        $(document).on('click', '#countingMongodb h3 a', function (e) {
            e.preventDefault();

            if ($(this).parents('#countingMongodb').hasClass('large-block')) { var newWidth = 150, newHeight = 150; }
            else { var newWidth = 310, newHeight = 310; }

            $(this).parents('#countingMongodb').animate({
                'height': newHeight,
                'width': newWidth
            }, 100, function () {
                $(this).toggleClass('large-block');
            });
        });
    };

    return _this.run();

});