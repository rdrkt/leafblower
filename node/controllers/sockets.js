
(function () {

    var _this = this;

    _this.run = function () {

        //get Socket IO lib
        _this.io = require('socket.io').listen(8080, { log: false });

        //server connection listener
        _this.io.sockets.on('connection', function (socket) {
            //everything else dependent on output.
            socket = socket;
        });

        //start data polling


        return _this;

    }

    _this.getData(type, callback) {
        
        var theData =   {
            'success':'1',
            'data':[
                {
                    'name':'foo',
                    'indexes':[],
                    'documentCount':5
                }
            ]
        };

        callback(theData);

    };

}).call(this);