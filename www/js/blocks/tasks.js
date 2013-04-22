
var tasks = (function () {

    var _this = this;

    _this.run = function () {
        //some markup? Yeh, g'wan.
        if ($('#tasks').length < 1) { $('main').append('<div class="block growing-block" id="tasks"><h3 class="expander-title"><a class="expander-link" href="" title=""></a></h3><div class="large-only"></div></div>'); }
        return _this;
    };

    _this.setData = function (data) {

        $('#tasks').find('h3').find('a').attr('title', data.total + ' Tasks').html(data.total + '<span>Tasks</span>');

        //get the large content block and empty, then inject content
        var $largeContent = $('#tasks').find('.large-only');
        $largeContent.empty();

        $.each(data, function(key, value) {
            if (key != 'total') {
                $largeContent.append('<p class="text-data"><span>' + key + '</span>' + value + '</p>');
            }
        });
    };

    _this.deleteBlock = function () {
        $('#tasks').remove();
    };

    _this.loadEvents = function () {


    };

    return _this.run();

});