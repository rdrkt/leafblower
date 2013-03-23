var adminCp = (function () {

    var _this = this;

    _this.run = function () {

        _this.fixDom();

        _this.loadEvents();

        return _this;
    };

    _this.fixDom = function () {
        var height = parseInt($('body').height() - $('header').outerHeight(true) - $('footer').outerHeight(true));
        $('aside').css('height', height + 'px');
        _this.loadBlockList();
    };

    _this.loadBlockList = function () {
        $listWrapper = $('#block-menu');

        $.ajax({
            url: '/api/block',
            dataType: 'json',
            complete: function (data) {
                $.each(data, function (key, type) {

                    if (type.blocks.length > 0) {
                        $listWrapper.append('<li><a href="" title="' + type.name + '" id="' + type.type + '">' + type.name + '</a><ul class="blocks-list"></ul></li>');
                        $blocksWrapper = $('#' + type.name).siblings('.blocks-list');
                        $.each(type.blocks, function (key, block) {
                            $blocksWrapper.append('<li><a href="" title="' + block.title + '" class="block-dragger">' + block.title + '</a></li>');
                            $blocksWrapper.find('li:last-child a').data({ 'desc': block.description, 'id': block.id, 'type': block.type });
                        });
                    }

                });
            }
        });
    };

    _this.loadEvents = function () {

        $(document).on('click', 'header nav a', function (e) {
            e.preventDefault();
            $newactive = $($(this).attr('href'));
            console.log($newactive);
            $('section.active').slideUp(200, function () {
                $newactive.slideDown(200, function () {
                    $(this).addClass('active');
                });
            });
        });

    };

    return _this.run();

});

$(document).ready(function () {
    var controlPanel = new adminCp();
});