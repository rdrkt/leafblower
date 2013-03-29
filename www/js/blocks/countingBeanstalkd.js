
var countingBeanstalkd = (function () {

    var _this = this;

    _this.run = function () {
        _this.loadEvents();

        return _this;
    };

    _this.setData = function (data) {
        var newTotal = 0;

        $('#countingBeanstalkd').find('.large-only').empty();

        $.each(data, function (key, item) {
            newTotal += parseInt(item.count);
            $('#countingBeanstalkd').find('.large-only').append('<p class="info-blob tubes"><span>' + item['_id'] + '</span>[' + item.count + ']</p>');
        });

        $('#countingBeanstalkd').find('h3').find('a').attr('title', newTotal + ' Tubes').html(newTotal + '<span>tubes</span>');
    };

    _this.deleteBlock = function () {
        $('#countingBeanstalkd').remove();
    };

    _this.loadEvents = function () {

        if ($('#countingBeanstalkd').length < 1) { $('main').append('<div class="block" id="countingBeanstalkd"><h3><a href="" title=""></a></h3><div class="large-only"></div></div>'); }

        $(document).on('click', '#countingBeanstalkd h3 a', function (e) {
            e.preventDefault();

            if ($(this).parents('#countingBeanstalkd').hasClass('large-block')) { var newWidth = 150, newHeight = 150; }
            else { var newWidth = 310, newHeight = 310; }

            $(this).parents('#countingBeanstalkd').animate({
                'height': newHeight,
                'width': newWidth
            }, 100, function () {
                $(this).toggleClass('large-block');
            });
        });
    };

    return _this.run();

});