
var blockController = (function () {

    var _this = this;

    _this.run = function () {

        return _this;
    };

    _this.handleData = function (jsonString) {
        var json = JSON.parse(jsonString);

        //output to console
        if (json.debug) {
            console.info(json);
        }

        //if the block isn't initialised, startup.
        if (!eval('_this.' + json.block)) {
            
            //if the instantiation fails, the block doesn't exist
            try {

                eval('_this.' + json.block + ' = new ' + json.block + '()');
                eval('_this.' + json.block + '.setData(' + JSON.stringify(json.data) + ')');

            } catch (err) {

                //Get the CSS if there is some.
                $.get('css/blocks/' + json.block + '.css', function (data, textStatus, jqxhr) {

                    //all loaded.
                    if (textStatus === 'success') {
                        $('head').append('<style type="text/css" id="style-' + json.block + '">' + data + '</script>');
                        eval('_this.' + json.block + ' = new ' + json.block + '()');
                        eval('_this.' + json.block + '.setData(' + JSON.stringify(json.data) + ')');
                    }

                    //fail safe in-case the block was sent but the JS isn't there
                })

                //Get the JS, stick it in the DOM, and run.
                $.get('js/blocks/' + json.block + '.js', function (data, textStatus, jqxhr) {

                    //all loaded.
                    if (textStatus === 'success') {
                        $('body').append('<script type="text/javascript" id="script-' + json.block + '">' + data + '</script>');
                        eval('_this.' + json.block + ' = new ' + json.block + '()');
                        eval('_this.' + json.block + '.setData(' + JSON.stringify(json.data) + ')');
                    }
                
                //fail safe in-case the block was sent but the JS isn't there
                }).fail(function () {
                    console.error('ERR ('+json.block+') :: BLOCK TYPE RECEIVED BUT NOT INTERPRETED');
                });
            }

        }

    };



    _this.handleDelete = function (blockId) {
        //if it's actually started and running, delete.
        if (!eval('_this.' + json.block)) {
            eval('_this.' + blockId + '.deleteBlock()');
        }
    };

    return _this.run();

});