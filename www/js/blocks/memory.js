
var memory = (function () {

    var _this = this;

    _this.run = function () {
        //some markup? Yeh, g'wan.
        if ($('#memory').length < 1) { $('main').append('<div class="block growing-block" id="memory"><h3 class="expander-title"><a class="expander-link" href="" title=""></a></h3><div class="large-only"></div></div>'); }
        return _this;
    };

    _this.setData = function (data) {

        var freePercentage = ((parseInt(data.free) / parseInt(data.total)) * 100).toFixed(2);
        
        $('#memory').find('h3').find('a').attr('title', freePercentage + '% Free memory').html(freePercentage + '<span>% Free memory</span>');

        //get the large content block and empty, then inject content
        var $largeContent = $('#memory').find('.large-only');
        $largeContent.empty();

        //loop through for more info
        $.each(data, function (key, value) {
            if (key != 'total') {
                $largeContent.append('<p class="text-data"><span>' + key + '</span>' + value + '</p>');
            }
        });
    };

    _this.deleteBlock = function () {
        $('#memory').remove();
    };

    return _this.run();

});