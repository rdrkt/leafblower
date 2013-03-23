var adminCp = (function () {

    var _this = this;

    _this.run = function () {

        _this.fixDom();

        _this.loadEvents();

        return _this;
    };

    _this.fixDom = function () {
        var height = parseInt($('body').height() - $('header').outerHeight(true) - $('footer').outerHeight(true));
        $('aside').css('min-height', height + 'px');
        _this.loadBlockList();
        _this.loadProfileList();
    };

    _this.loadBlockList = function () {
        _this.showLoader();

        $listWrapper = $('#block-menu');

        //empty out list
        $listWrapper.empty();

        //AJAXCOMMENT$.get('/api/block', function (data) {
        var data = JSON.parse('[{ "name": "Counting", "type": "counting", "blocks": [{ "_id": "countingBeanstalkd", "type": "counting", "title": "Beanstalkd Tube Counting", "description": "Block for visualizing the size of tubes and the workers that are watching them", "ttl": 1000, "options": { "value1": "default", "value2": "default2"} }, { "_id": "countingMongodb", "type": "counting", "title": "Mongodb Collection Counting", "description": "Block for visualizing the size of collections and their indexes", "ttl": 1000, "options": { "value1": "default", "value2": "default2"}}] }, { "name": "Geospacial", "type": "geospacial", "blocks": [{ "_id": "geoCheckIns", "type": "geospacial", "title": "Checkin visualiser", "description": "Block for visualizing the checkins occuring in your app bound by a circle centered at a given lat\/lon and radius", "ttl": 5000, "options": { "lat": -33.873651, "lon": -151.2068896, "radius": 50, "collection": "checkins"}}] }, { "name": "Filesystem", "type": "filesystem", "blocks": [{ "_id": "gridfsView", "type": "filesystem", "title": "Mongodb GridFS Viewer", "description": "View all the files in your GridFS collection as if they are in a physical storage volume", "ttl": 1000, "options": []}]}]'); ;

        $.each(data, function (key, type) {
            if (type.blocks.length > 0) {
                $listWrapper.append('<li><a href="" title="' + type.name + '" id="' + type.type + '">' + type.name + '</a><ul class="blocks-list"></ul></li>');
                $blocksWrapper = $('#' + type.type).siblings('.blocks-list');
                $.each(type.blocks, function (key, block) {
                    $blocksWrapper.append('<li><a href="" title="' + block.title + '" class="block-dragger">' + block.title + '</a></li>');
                    $blocksWrapper.find('li:last-child a').data({ 'desc': block.description, 'id': block.id, 'type': block.type });
                });
            }

        });

        $listWrapper.children('li:first-child').children('ul').addClass('open');
        $listWrapper.children('li:first-child').children('a').addClass('open');


        //AJAXCOMMENT});

        _this.hideLoader();
    };

    _this.loadProfileList = function () {
        _this.showLoader();

        //AJAXCOMMENT$.get('/api/profile', function (data) {



        //AJAXCOMMENT});

        _this.hideLoader();
    };

    _this.loadEvents = function () {

        $(window).on('resize', function () {
            _this.fixDom();
        });

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

        $(document).on('click', '#block-menu > li > a', function (e) {
            e.preventDefault();
            $(this).siblings('ul').slideToggle(100);
            $(this).toggleClass('open');
            $(this).siblings('ul').toggleClass('open');
        });

    };

    _this.showLoader = function () {
        $('#loading-overlay, #loading-gif').fadeIn(50);
    };

    _this.hideLoader = function () {
        $('#loading-overlay, #loading-gif').fadeOut(50);
    };

    return _this.run();

});

$(document).ready(function () {
    var controlPanel = new adminCp();
});