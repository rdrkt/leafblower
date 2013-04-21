

var listPage = (function () {

    var _this = this;

    _this.run = function () {

        _this.loadList();

        return _this;
    };

    _this.loadList = function () {
        $.get('http://admin.leafblower.rdrkt.com/api/profile/list', function (data) {
            $.each(data.data, function (key, profile) {
                $('#profile-list').append('<li><a href="view.php#' + profile['_id'] + '" title="' + profile['name'] + '">' + profile['name'] + '</a></li>');
            });
        }, 'json');
    };

    return _this.run();
});

$(document).ready(function () {
    var listController = new listPage();
});