
var blockController = (function () {

    var _this = this;

    _this.run = function () {


        return _this;
    };

    _this.handleData = function (jsonString) {
        var json = JSON.parse(jsonString);

        if (!eval('_this.' + json.block)) {
            eval('_this.' + json.block + ' = new ' + json.block + '()');
        }

        eval('_this.' + json.block + '.setData(' + JSON.stringify(json.data) + ')');

    };

    _this.handleDelete = function (blockId) {
        eval('_this.' + blockId + '.deleteBlock()');
    };

    return _this.run();

});