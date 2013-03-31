
var gridfsView = (function () {

    var _this = this;

    _this.run = function () {
        if ($('#gridfsView').length < 1) { $('main').append('<div class="block" id="gridfsView"><h5>gridfsView</h5><div class="large-only"></div></div>'); }

        return _this;
    };

    _this.setData = function (data) {
        //console.log('gridfsView data...');
        //console.log(data);
    };

    _this.deleteBlock = function () {
        //perform self removal and cleanup
    };

    return _this.run();

});