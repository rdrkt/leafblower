
(function () {

    var _this = this;

    _this.run = function () {

        //get Socket IO lib
        _this.io = require('socket.io').listen(8080, { log: false });

        //server connection listener
        _this.io.sockets.on('connection', function (socket) {
            //room joiner
            socket.on('join room', function (room) {
                socket.join(room);
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

                for (i = 0; i < profileLists.length; i++) {

                    var profile = profileLists[i];

                    for (ii = 0; ii < profile.blocks.length; ii++) {
                        var block = profile.blocks[ii];
                        _this.getData('/api/live/' + profile['_id'] + '/' + block['_id'], profile['_id'], block['_id'], block['ttl']);
                    }

                }

            });
        }).on('error', function (e) {
            console.log("Got error: " + e.message);
            _this.getData('/api/live/' + profile['_id'] + '/' + block['_id'], profile['_id'], block['_id'], block['ttl']);
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
                if (blockId == 'countingMongodb') { console.log(json); console.log(__App.config.baseApiDomain + url); }
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