
var countingBeanstalkd = (function () {

    var _this = this;

    //store latest data block.
    _this.data = [];

    _this.run = function () {

        //some pretty css for the block
        $('head').append('<link rel="stylesheet" href="css/blocks/countingBeanstalkd.css" />');
        //some markup? Yeh, g'wan.
        if ($('#countingBeanstalkd').length < 1) { $('main').append('<div class="block expandable-block" id="countingBeanstalkd"><h3><a href="" title=""></a></h3><div id="countingBeanstalkd-large" class="large-only clearfix"></div></div>'); }
        //DOM events? Sure!
        _this.loadEvents();

        return _this;
    };

    _this.setData = function (data) {
        var newTotal = 0;

        //$('#countingBeanstalkd').find('.large-only').empty();

        //add the data block if it doesn't exist already
        if ($('#countingBeanstalkd-large').find('#countingBeanstalkd-data').length < 1) {
            $('#countingBeanstalkd-large').append('<div id="countingBeanstalkd-data"></div>');
        }
        //get the data box
        var $dataParent = $('#countingBeanstalkd-large').find('#countingBeanstalkd-data');

        //empty out data wrapper, append empty instructional box
        var activeId = $('#countingBeanstalkd-large').find('.active-data').attr('id');
        $dataParent.empty();

        //add in the data display items
        $.each(data, function (key, item) {
            //increase the total for title
            newTotal += parseInt(item.count);
            //get the class, if it's current active, add active class
            var dataClass = (item._id == activeId) ? 'active-data data-wrapper' : 'data-wrapper';
            
            //add the data wrapper
            $dataParent.append('<div class="' + dataClass + '" id="' + item._id + '"><h4>Tube ' + item['_id'] + ' stats[' + item.count + ']</h4><ul id="' + item._id + '-list"></ul></div>');
            //chuck in the tube stats
            $.each(item.tubestats, function (label, value) {
                if (label != 'name') {
                    $('#' + item._id + '-list').append('<li><h5>' + label + '</h5><p>' + value + '</p></li>');
                }
            });
        });

        //if the currently selected item no longer exists, or there wasn't one. inject dummy instructional and disable pre-clicked graph element
        if ($('#countingBeanstalkd-large').find('.active-data').length < 1) {
            $('#countingBeanstalkd-large').find('.arc').removeClass('.active-job');
            $dataParent.append('<div class="active-data data-wrapper"><h4>Click on the pie chart items for more information.</h4></div>');
        }

        
        $('#countingBeanstalkd').find('h3').find('a').attr('title', newTotal + ' Jobs').html(newTotal + '<span>Jobs</span>');

        //graph isn't live yet.
        if ($('#countingBeanstalkd-graph').length < 1) {
            _this.createPieChart(data, 240, 450, 25, 'countingBeanstalkd-graph', '#countingBeanstalkd-large');
        }
    };

    _this.deleteBlock = function () {
        $('#countingBeanstalkd').remove();
    };

    _this.loadEvents = function () {

        //added event for expanding content - get rid of graph, it'll get redrawn later
        $(document).on('click', '#countingBeanstalkd h3 a', function (e) {
            $('#countingBeanstalkd-graph').remove();
        });

        //handle data toggling
        $(document).on('click', '#countingBeanstalkd-graph path, #countingBeanstalkd-graph text', function (e) {
            //get parent arc
            var $arc = $(this).parent();
            //do nothing if this is the currently selected item
            if ($arc.is('.active-job')) { return; }
            //take active class from any other block and add it to this one
            $('#countingBeanstalkd .arc').removeClass('active-job');
            $arc.addClass('active-job');
            //get chunk
            $dataWrapper = $('#countingBeanstalkd-data');
            $dataWrapper.find('.active-data').fadeOut(200, function () {
                $(this).removeClass('active-data');
                $dataWrapper.find('#' + $arc.data('id')).fadeIn(200, function () {
                    $(this).addClass('active-data');
                });
            });
        });

    };
    
    _this.createPieChart = function (data, height, width, padding, cssId, parentId) {

        //get rid of previous chart if exists
        $('#' + cssId).remove();

        var radius = Math.min(width, (height - (padding * 2))) / 2;

        var color = d3.scale.ordinal()
            .range(["#113F8C", "#01A4A4", "#00A1CB", "#61AE24", "#D0D102", "#32742C", "#D70060", "#E54028", "#F18D05", "#616161"]);

        var arc = d3.svg.arc()
            .outerRadius(radius - 10)
            .innerRadius(0);

        var pie = d3.layout.pie()
            .sort(null)
            .value(function (d) { return d.count; });
        
        var svg = d3.select(parentId).append("svg")
            .attr("width", width)
            .attr("height", height)
            .attr('id', cssId)
            .append("g")
            .attr("transform", "translate(" + width / 2 + "," + height / 2 + ")");

        data.forEach(function (d) {
            d.count = +d.count;
        });

        var g = svg.selectAll(".arc")
            .data(pie(data))
            .enter().append("g")
            .attr("class", "arc")
            .attr('data-id', function (d) {
                return d.data._id
            });

        g.append("path")
            .attr("d", arc)
            .style("fill", function (d) { return color(d.data._id); });

        g.append("text")
            .attr("transform", function (d) {
                var c = arc.centroid(d),
                    x = c[0],
                    y = c[1],
                    // pythagorean theorem for hypotenuse
                    h = Math.sqrt(x * x + y * y);
                return "translate(" + (x / h * Math.round((height - (padding * 2)) / 2)) + ',' + (y / h * Math.round((height - (padding * 2)) / 2)) + ")";
            })
            .attr("dy", ".35em")
            .style("text-anchor", function (d) {
                // are we past the center?
                return (d.endAngle + d.startAngle) / 2 > Math.PI ? "end" : "start";
            })
            .text(function (d) { return d.data._id + ' ('+d.data.count+')'; });
        
    };

    return _this.run();

});