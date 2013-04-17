
(function () {

    var _this = this;

    //store block data temporarily to stop flooding the API when 1 block is in 50000 profiles
    _this.blockCache = {};

    //where profiles are stored
    _this.profileBlocks = null;

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
            path: '/api/profile/list'
        };

        var start = new Date();
        http.get(apiOptions, function (response) {

            response.setEncoding('utf8');
            response.on('data', function (json) {
                //for debuggin
                //console.log('[Profile list] Request took:', new Date() - start, 'ms');

                //run the profile/blocks setter/handler
                _this.setProfiles(json);

                //we don't want lag buildup or profile updates to require node.js restart, check profiles and restart queue periodically
                _this.queueProcessor.add(function () {
                    _this.queueProcessor.stop();
                    _this.queueProcessor.start();
                    _this.loadProfiles();
                }, __App.config.processQueueRestart);
            });



        }).on('error', function (e) {
            console.log('[Profile List Err] : ' + e.message);

            //we don't want lag buildup or profile updates to require node.js restart, check profiles and restart queue periodically
            _this.queueProcessor.add(function () {
                _this.queueProcessor.stop();
                _this.queueProcessor.start();
                _this.loadProfiles();
            }, 1000);
        });

    };

    //setup profiles, emit block deletions, startup block api requests
    _this.setProfiles = function (json) {

        var profileLists = JSON.parse(json).data;

        //store old profile blocks for deletions
        var oldProfileBlocks = _this.profileBlocks;

        //reset profile blocks
        _this.profileBlocks = {};

        for (i = 0; i < profileLists.length; i++) {

            var profile = profileLists[i];
            _this.profileBlocks[profile['_id']] = profile.blocks;

            for (ii = 0; ii < profile.blocks.length; ii++) {
                var block = profile.blocks[ii];
                //set the timeout into cache list (so it could be cleared)
                _this.getData('/api/live/' + profile['_id'] + '/' + block['_id'], profile['_id'], block['_id'], block['ttl']);
            }

        }

        //compare existing profiles (if this isn't the first run)
        if (typeof oldProfileBlocks == 'object') {
            _this.emitBlockDeletions(oldProfileBlocks);
        }

    };

    //compare last fetched profile list vs. current for block differences, emit delete request if blocks are missing
    _this.emitBlockDeletions = function (oldProfileList) {

        //loop through old profile comparing against new
        for (var oldProfile in oldProfileList) {

            //has the entire profile been deleted?
            if (_this.profileBlocks[oldProfile]) {

                //loop through the blocks in the old profile
                for (i = 0; i < oldProfileList[oldProfile].length; i++) {
                    //block isn't the same, emit delete
                    if (oldProfileList[oldProfile][i]['_id'] != _this.profileBlocks[oldProfile][i]['_id']) {
                        _this.broadcastData('blockDelete', oldProfile, { 'id': oldProfileList[oldProfile][i]['_id'] });
                    }
                }

            } else {
                _this.broadcastData('deleteProfile', oldProfile, { 'removed': true });
            }

        }
    };

    //fetch data and emit to profile viewers
    _this.getData = function (url, profileId, blockId, ttl) {

        //no point grabbing data for a block that's not in use
        if (_this.io.sockets.clients(profileId).length > 0) {

            //console.log(_this.blockCache[profileId + '-' + blockId]);

            //check the cache for this block.
            if (!_this.blockCache[profileId + '-' + blockId]) {

                var http = require('http');

                var apiOptions = {
                    host: __App.config.baseApiDomain,
                    method: 'GET',
                    path: url
                };

                //grab JSON data over http
                var start = new Date();
                http.get(apiOptions, function (response) {
                    response.setEncoding('utf8');
                    response.on('data', function (json) {

                        //for debugging
                        //console.log('[' + profileId + ' - ' + blockId + '] Request took:', new Date() - start, 'ms');


                        try {
                            var json = JSON.parse(json);
                            json = { 'block': blockId, 'data': json };
                            _this.broadcastData('data', profileId, json);

                            //add data to the cache, and set a timeout to delete the cache in 50% of ttl
                            //so the same profile viewer doesn't get the same cached item
                            _this.blockCache[profileId + '-' + blockId] = json;
                            _this.queueProcessor.add(function () { delete _this.blockCache[profileId + '-' + blockId]; }, parseInt(ttl / 2));

                        } catch (err) {
                            console.log(err);
                        }

                        //and add back to queue
                        _this.queueProcessor.add(function () {
                            _this.getData(url, profileId, blockId, ttl);
                        }, ttl);
                    });

                }).on('error', function (e) {
                    console.log('[Block Err] : ' + e.message);

                    //just add back to queue to not overwhelm API
                    _this.queueProcessor.add(function () {
                        _this.getData(url, profileId, blockId, ttl);
                    }, ttl);
                });

                //if cache exists, use.
            } else {

                //incase the cache managed to get dropped in the last processing tick
                var cache = _this.blockCache[profileId + '-' + blockId];

                //if it was dropped (undefined), just re-run the getData.
                if (!cache) {
                    _this.getData(url, profileId, blockId, ttl);
                } else {

                    //emit cached version
                    _this.broadcastData('data', profileId, cache);

                    //and add back to queue
                    _this.queueProcessor.add(function () {
                        _this.getData(url, profileId, blockId, ttl);
                    }, ttl);

                }
            }

            //if noone to receive currently, just queue to recheck on the given ttl
        } else {

            //and add back to queue
            _this.queueProcessor.add(function () {
                _this.getData(url, profileId, blockId, ttl);
            }, ttl);

        }
    }



    //only broadcast if there is someone in that profile logged in and viewing it
    _this.broadcastData = function (msgType, profileRoomId, jsonObject) {
        if (_this.io.sockets.clients(profileRoomId).length > 0) {
            //using .['in'] rather than .in as JS lint spacks out.
            _this.io.sockets['in'](profileRoomId).emit(msgType, JSON.stringify(jsonObject));
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
        },

        execute: function () {
            for (i = 0; i < this.list.length; i++) {

                if ((this.list[i][1] - __App.config.tickerSpeed) < 1) {
                    this.list[i][0]();
                    this.list.splice(i, 1);
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