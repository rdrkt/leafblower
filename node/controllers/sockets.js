
(function () {

    var _this = this;

    _this.blockCache = {};

    //constructor
    _this.run = function () {

        //get Socket IO lib
        _this.io = require('socket.io').listen(8080, { log: false });

        //server connection listener
        _this.io.sockets.on('connection', function (socket) {
            //room joiner
            socket.on('join', function (room) {
                console.log('join received');
                socket.emit('joined', room);
                socket.join(room);
            });
        });

        //run queue processor
        _this.queueProcessor.start();

        //start data polling
        _this.loadProfiles();

        return _this;

    };

    //load profiles.
    _this.loadProfiles = function () {
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
                        //set the timeout into cache list (so it could be cleared)
                        _this.queueProcessor.add(function () { _this.getData('/api/live/' + profile['_id'] + '/' + block['_id'], profile['_id'], block['_id'], block['ttl']); }, block['ttl']);

                    }

                }

                //we don't want lag buildup or profile updates to require node.js restart, check profiles and restart queue periodically
                _this.queueProcessor.add(function () {
                    _this.queueProcessor.stop();
                    _this.queueProcessor.start();
                    _this.loadProfiles();
                }, __App.config.processQueueRestart);
            });

        }).on('error', function (e) {
            console.log("Got error: " + e.message);
        });

    };

    //fetch data and emit to profile viewers
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
                    _this.broadcastData(profileId, json);
                } catch (err) {
                    console.log(err);
                }

                //and add back to queue
                _this.queueProcessor.add(function () { _this.getData(url, profileId, blockId, ttl); }, ttl);
            });

        });

    };

    //only broadcast if there is someone in that profile logged in and viewing it
    _this.broadcastData = function (profileRoomId, jsonObject) {
        if (_this.io.sockets.clients(profileRoomId).length > 0) {
            _this.io.sockets['in'](profileRoomId).emit('data', JSON.stringify(jsonObject));
        }
    };

    //queue processor, handles everything in pre-allocated millisecond incrememnts
    _this.queueProcessor = {

        list: [],

        start: function () {
            this.interval = setInterval(function () { _this.queueProcessor.execute() }, __App.config.tickerSpeed);
        },

        add: function (callback, milliseconds) {
            this.list.push([callback, milliseconds]);
            console.log('add/readd');
        },

        remove: function (queueKey) {
            var newArray = new Array();
            for (var i = 0; i < this.list.length; i++) {
                if (i != queueKey) {
                    newArray.push(this.list[i]);
                }
            }
            this.list = newArray;
        },

        execute: function () {
            //console.log(this.list.length);
            for (i = 0; i < this.list.length; i++) {
                if ((this.list[i][1] - __App.config.tickerSpeed) < 1) {
                    this.list[i][0]();
                    this.remove(i);
                } else {
                    this.list[i][1] = parseInt(this.list[i][1] - 100);
                }
            }

        },

        stop: function () {
            clearInterval(this.interval);
            this.list = [];
        }

    };

    return _this.run();

}).call(this);