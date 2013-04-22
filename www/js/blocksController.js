
var blockController = (function () {

    var _this = this;

    _this.run = function () {

        return _this;
    };

    _this.handleData = function (jsonString) {
        var json = JSON.parse(jsonString);

        //output to console
        if (json.debug) {
            console.info(json);
        }

        //if the block isn't initialised, startup.
        if (!eval('_this.' + json.block)) {
            eval('_this.' + json.block + ' = new ' + json.block + '()');
        }

        //in-case some block is added in the back but not running on the front.
        try {
            eval('_this.' + json.block + '.setData(' + JSON.stringify(json.data) + ')');
        } catch (err) {
            console.error('ERR :: BLOCK TYPE PASSED BUT NOT INTERPRETED');
            console.error(err);
        }

    };

    _this.handleDelete = function (blockId) {
        //if it's actually started and running, delete.
        if (!eval('_this.' + json.block)) {
            eval('_this.' + blockId + '.deleteBlock()');
        }
    };

    return _this.run();

});