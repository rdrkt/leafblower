
var geoCheckIns = (function () {

    var _this = this;

    _this.run = function () {
        if ($('#geoCheckIns').length < 1) { $('main').append('<div class="block" id="geoCheckIns"><h5>geoCheckIns</h5><div class="large-only"></div></div>'); }

        return _this;
    };

    _this.setData = function (data) {
        //console.log('geoCheckIns data...');
        //console.log(data);
    };

    _this.deleteBlock = function () {
        //perform self removal and cleanup
    };

    return _this.run()

});