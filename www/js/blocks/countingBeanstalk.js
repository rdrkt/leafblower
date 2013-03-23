
(function (data, options) {

    var _this = this;

    _this.data = data;
    _this.otions = options;

    _this.run = function () {

        _this.addToDom();
        _this.loadData(data);

    };

    _this.addToDom = function () {
        var divWrapper = document.createElement('div');
        divWrapper.setAttribute('id', 'countingBeanstalk');

    };

    _this.loadData = function (data) {

    };

    _this.unload = function () {

    };

    return _this.run();

});