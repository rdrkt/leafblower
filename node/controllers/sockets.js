
(function () {

    var _this = this;

    _this.run = function () {

        //get Socket IO lib
        _this.io = require('socket.io').listen(8080, { log: false });

        //server connection listener
        _this.io.sockets.on('connection', function (socket) {
            //room joiner
            socket.on('join room', function (room) {
                try {
                    if (typeof _this.profileBlocks != undefined) {
                        socket.emit('blockControllers', JSON.stringify(_this.profileBlocks[room]));
                    }
                    socket.join(room);
                } catch(err) {                    
                    setTimeout(function() {
                        try {
                            if (typeof _this.profileBlocks != undefined) {
                                socket.emit('blockControllers', JSON.stringify(_this.profileBlocks[room]));
                            }
                            socket.join(room);
                        } catch (err) {
                            console.log('profiles are taking ages to grab');
                        }
                    }, 1000); 

                }
            });
        });

        //start data polling
        _this.startProfiles();

        return _this;

    };

    _this.startProfiles = function () {
        var http = require('http');

        var apiOptions = {
            host: __App.config.baseApiDomain,
            method: 'GET',
            path: '/api/profile'
        };

        http.get(apiOptions, function (response) {

            response.setEncoding('utf8');
            response.on('data', function (json) {

                var profileLists = JSON.parse(json);
                _this.profileBlocks = {};

                for (i = 0; i < profileLists.length; i++) {

                    var profile = profileLists[i];
                    _this.profileBlocks[profile['_id']] = profile.blocks;

                    for (ii = 0; ii < profile.blocks.length; ii++) {
                        var block = profile.blocks[ii];
                        _this.getData('/api/live/' + profile['_id'] + '/' + block['_id'], profile['_id'], block['_id'], block['ttl']);
                    }

                }

            });

        }).on('error', function (e) {
            console.log("Got error: " + e.message);
        });

    };

    _this.getData = function (url, profileId, blockId, ttl) {

        var http = require('http');

        var apiOptions = {
            host: __App.config.baseApiDomain,
            method: 'GET',
            path: url
        };

        http.get(apiOptions, function (response) {

            response.setEncoding('utf8');
            response.on('data', function (json) {
                try {
                    var json = JSON.parse(json);
                    json = { 'block': blockId, 'data': json };
                    _this.io.sockets.in(profileId).emit('data', JSON.stringify(json));
                } catch (err) {
                    console.log(err);
                }

                setTimeout(function () { _this.getData(url, profileId, blockId, ttl); }, ttl);
            });

        });

    };

    return _this.run();

}).call(this);