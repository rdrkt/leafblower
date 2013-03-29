var adminCp = (function () {

    var _this = this;

    //config base url
    _this.baseUrl = 'http://admin.leafblower.rdrkt.com';

    _this.run = function () {

        _this.fixDom();

        _this.loadEvents();

        return _this;
    };

    _this.fixDom = function () {
        var height = parseInt($('body').height() - $('header').outerHeight(true) - $('footer').outerHeight(true));
        $('aside').css('min-height', height + 'px');
        _this.loadBlockList();
    };

    _this.loadBlockList = function () {
        _this.showLoader();

        $listWrapper = $('#block-menu');

        //empty out list
        $listWrapper.empty();

        $.get(_this.baseUrl + '/api/block', function (data) {
            //var data = JSON.parse('[{ "name": "Counting", "type": "counting", "blocks": [{ "_id": "countingBeanstalkd", "type": "counting", "title": "Beanstalkd Tube Counting", "description": "Block for visualizing the size of tubes and the workers that are watching them", "ttl": 1000, "options": { "value1": "default", "value2": "default2"} }, { "_id": "countingMongodb", "type": "counting", "title": "Mongodb Collection Counting", "description": "Block for visualizing the size of collections and their indexes", "ttl": 1000, "options": { "value1": "default", "value2": "default2"}}] }, { "name": "Geospacial", "type": "geospacial", "blocks": [{ "_id": "geoCheckIns", "type": "geospacial", "title": "Checkin visualiser", "description": "Block for visualizing the checkins occuring in your app bound by a circle centered at a given lat\/lon and radius", "ttl": 5000, "options": { "lat": -33.873651, "lon": -151.2068896, "radius": 50, "collection": "checkins"}}] }, { "name": "Filesystem", "type": "filesystem", "blocks": [{ "_id": "gridfsView", "type": "filesystem", "title": "Mongodb GridFS Viewer", "description": "View all the files in your GridFS collection as if they are in a physical storage volume", "ttl": 1000, "options": []}]}]'); ;

            $.each(JSON.parse(data), function (key, type) {
                if (type.blocks.length > 0) {
                    $listWrapper.append('<li><a href="" title="' + type.name + '" id="' + type.type + '">' + type.name + '</a><ul class="blocks-list"></ul></li>');
                    $blocksWrapper = $('#' + type.type).siblings('.blocks-list');
                    $.each(type.blocks, function (key, block) {
                        var stringifiedOptions = encodeURI(JSON.stringify(block.options));
                        $blocksWrapper.append('<li><a href="" title="' + block.title + '" id="' + block._id + '" data-id="' + block._id + '" data-type="' + block.type + '" data-title="' + block.title + '" data-description="' + block.description + '" data-ttl="' + block.ttl + '" data-options="' + stringifiedOptions + '" class="block-dragger">' + block.title + '</a></li>');
                        $('#' + block._id).draggable({
                            snap: '.profile-block-list',
                            snapMode: 'inner',
                            revert: true,
                            revertDuration: 0,
                            start: function () {
                                $('.profile-block-list').addClass('drag-here');
                            },
                            stop: function () {
                                $('.profile-block-list').removeClass('drag-here');
                            }
                        });
                    });
                }

            });

            $listWrapper.children('li:first-child').children('ul').addClass('open');
            $listWrapper.children('li:first-child').children('a').addClass('open');


            _this.hideLoader();
            _this.loadProfileList();
        });

    };

    _this.loadProfileList = function () {
        _this.showLoader();

        $.get(_this.baseUrl + '/api/profile', function (data) {

            $('#list-profiles ul').empty();

            var profileMarkup = '', prefix;

            $.each(data, function (k, profile) {

                prefix = profile['_id'];

                profileMarkup += '<li class="update-block-wrapper clearfix">';
                profileMarkup += '<h3><a href="" title="' + profile['name'] + '" class="toggle-profile-update">' + profile['name'] + '</a></h3>';

                profileMarkup += '<form name="frm-update-profile" class="profile-sender">';
                profileMarkup += '<div class="field-wrapper clearfix" data-id="' + profile['_id'] + '">';
                profileMarkup += '<label for="txt' + prefix + 'ProfileName">Profile Name</label>';
                profileMarkup += '<input type="text" id="txt' + prefix + 'ProfileName" value="' + profile['name'] + '" name="txt' + prefix + 'ProfileName" class="textbox profile-name" />';
                profileMarkup += '</div>';
                profileMarkup += '<div class="field-wrapper clearfix">';
                profileMarkup += '<label for="txt' + prefix + 'ProfileDescription">Profile Description</label>';
                profileMarkup += '<textarea id="txt' + prefix + 'ProfileDescription" name="txt' + prefix + 'ProfileDescription" class="profile-description">' + profile['description'] + '</textarea>';
                profileMarkup += '</div>';
                profileMarkup += '<div class="profile-block-list">';
                profileMarkup += '<input type="hidden" value="' + encodeURI(JSON.stringify(profile['blocks'])) + '" class="blockJson" name="newBlockJson" />';

                $.each(profile['blocks'], function (key, block) {
                    profileMarkup += '<div class="added-block"><span>' + $('#' + block['_id']).attr('title') + '</span><input type="text" size="5" name="ttl-' + block['_id'] + '" class="update-ttl" value="' + block['ttl'] + '" /></div>';
                });

                profileMarkup += '</div>';
                profileMarkup += '<a href="" class="delete-profile" data-profileid="' + profile['_id'] + '" title="Delete ' + profile['name'] + '">Delete ' + profile['name'] + '</a>';
                profileMarkup += '<input type="hidden" class="hidden-profile-id" value="' + profile['_id'] + '" name="hdn' + prefix + 'ProfileId" />';
                profileMarkup += '<input type="submit" name="button-save-profile" class="button-save-profile" value="Update profile" />';
                profileMarkup += '</form>';

                profileMarkup += '</li>';

            });

            $('#list-profiles ul').append(profileMarkup);

            $('#list-profiles').find('.profile-block-list').droppable({
                accept: '.block-dragger',
                drop: function (event, ui) {
                    _this.addBlockToProfile($(this), event, ui);
                }
            });

            _this.hideLoader();
        }, 'json');
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

        $(document).on('click', '#block-menu a', function (e) {
            e.preventDefault();
            if (!$(this).hasClass('block-dragger')) {
                $(this).siblings('ul').slideToggle(100);
                $(this).toggleClass('open');
                $(this).siblings('ul').toggleClass('open');
            }
        });

        //new/existing profile submission
        $(document).on('click', '.button-save-profile', function (e) {
            e.preventDefault();

            _this.showLoader();

            //syntax:  {_id: "my-awesome-profile", name: "My Awesome Profile", description: "My super awesome profile has everything!", blocks: [ { _id:"countingMongodb", options:{}, ttl: 100 }, {_id: "countingBeanstalkd", options : { }, ttl: 100 }]}
            var profile = {};
            var $form = $(this).parent();

            if ($form.find('.hidden-profile-id').length > 0) { profile['_id'] = $form.find('.hidden-profile-id').val(); }

            profile['name'] = $form.find('.profile-name').val();
            profile['description'] = $form.find('.profile-description').val();
            profile['blocks'] = JSON.parse(decodeURI($form.find('.blockJson').val()));

            console.log(profile);

            $.post(_this.baseUrl + '/api/profile', profile, function () {

                alert('Profile saved');
                _this.hideLoader();
                _this.loadProfileList();

            }, 'json');

        });

        //delete profile
        $(document).on('click', '.delete-profile', function (e) {
            e.preventDefault();

            _this.deleteProfile(profileId);

            var profileId = $(this).data('profileid');
            $.ajax({
                type: 'DELETE',
                url: _this.baseUrl + '/profile/' + profileId,
                success: function (data) {
                    console.log('deleted?');
                    console.log(data);
                }
            });

        });

        //toggle open profile
        $(document).on('click', '.toggle-profile-update', function (e) {
            e.preventDefault();
            $(this).parent().siblings('form').slideToggle(150);
            $(this).toggleClass('expanded');
        });

        //fakes click on form submit from "return/enter/space"
        $(document).on('submit', '.profile-sender', function (e) {
            e.preventDefault();
            $(this).find('.button-save-profile').click();
        });

        //reset for new profiles
        $(document).on('click', '.button-reset-profile', function (e) {
            $(this).siblings('.profile-block-list').find('div').remove();
            $(this).siblings('.profile-block-list').find('.blockJson').val('');
        });

        //what happens when a block is dropped on profile
        $('.profile-block-list').droppable({
            accept: '.block-dragger',
            drop: function (event, ui) {
                _this.addBlockToProfile($(this), event, ui);
            }
        });

        //update ttl when defocus
        $(document).on('blur', '.update-ttl', function () {
            var $this = $(this);
            var $jsonInput = $this.parents('.profile-block-list').find('input.blockJson');
            var blockList = JSON.parse(decodeURI($jsonInput.val()));

            $.each(blockList, function (key, block) {
                if ($this.parent().attr('val') == block['_id']) {
                    blockList[key]['ttl'] = parseInt($this.val());
                }
            });
        });

    };

    _this.addBlockToProfile = function ($that, event, ui) {

        var $link = $(ui.draggable).clone().removeAttr('style').removeAttr('class');
        var newDiv = document.createElement('div');
        $.each($link[0].attributes, function (key, attr) {
            newDiv.setAttribute(attr.nodeName, attr.value);
        });
        newDiv.setAttribute('class', 'added-block');
        $that.append($(newDiv).html('<span>' + $link.attr('title') + '</span><input type="text" size="5" name="ttl-' + $link.attr('id') + '" class="update-ttl" value="' + $link.data('ttl') + '" />'));

        var blockList = $that.find('input.blockJson').val();
        if (blockList != '') {
            blockList = JSON.parse(decodeURI(blockList));
        } else {
            blockList = [];
        }

        blockList.push({ '_id': $link.attr('id'), 'options': {}, 'ttl': $link.data('ttl') });

        $that.find('input.blockJson').val(encodeURI(JSON.stringify(blockList)));
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