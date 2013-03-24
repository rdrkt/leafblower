
var blockController = (function () {

    var _this = this;

    _this.run = function () {


        return _this;
    };

    _this.handleData = function (jsonString) {
        var json = JSON.parse(jsonString);

        if (eval('_this.' + json.block)) {
            //console.log(eval('_this.' + json.block));
        } else {
            eval('_this.' + json.block + ' = new ' + json.block + '()');
        }

        eval('_this.' + json.block + '.setData(' + JSON.stringify(json.data) + ')');

    };
    
    return _this.run();

});