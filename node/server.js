
(function () {

    //_this
    __App = this;

    //get config data
    __App.config = require('./controllers/config.js');

    //get the Socket controller
    __App.sockets = require('./controllers/auth.js');

    //get the Socket controller
    __App.sockets = require('./controllers/sockets.js');


}).call(this);