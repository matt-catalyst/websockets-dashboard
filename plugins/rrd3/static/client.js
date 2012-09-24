plugins.rrd3 = {

    /**
     * This prevents the plugin frontend from receiving data when the dashboard
     * is hidden (e.g. a different tab is open). This property is optional.
     */
    noBackgroundData: true,

    html: null,
    margin: null,
    width: null,
    height: null,
    svg: null,
    x: null,
    y: null,
    xAxis: null,
    yAxis: null,
    line: null,
    area: null,
    data: null,

    start: function() {
        html = ('<div class="plugin" id="rrd3"><h1>RRd3</h1></div>');
        $('div#body').append(html);

        data = {};
        margin = {top: 10, right: 30, bottom: 60, left: 70},
        width =  $('#rrd3').width()- margin.left - margin.right;
        height = $('#rrd3').height() -margin.top - margin.bottom;


        x = d3.time.scale()
            .domain([d3.min(d3.keys(data)), d3.max(d3.keys(data))])
            .range([0, width]);

        y = d3.scale.linear()
            .domain([0, d3.max(d3.values(data))])
            .range([height, 0]);

        xAxis = d3.svg.axis()
            .scale(x)
            .tickSubdivide(true)
            .orient("bottom");

        yAxis = d3.svg.axis()
            .scale(y)
            .orient("left");

        line = d3.svg.line()
            .interpolate("monotone")
            .x(function(d) { return x(d.key); })
            .y(function(d) { return y(d.value); });

        area = d3.svg.area()
            .interpolate("monotone")
            .x(line.x())
            .y1(line.y())
            .y0(y(0));

        svg = d3.select("#rrd3").append("svg")
            .datum(function() { return(d3.entries(data)); })
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");


        svg.append("path")
            .attr("class", "area")
            .attr("d", area);

        svg.append("g")
            .attr("class", "x axis")
            .attr("transform", "translate(0," + height + ")")
            .call(xAxis);

        svg.append("g")
            .attr("class", "y axis")
            .call(yAxis);

        svg.append("path")
            .attr("class", "line")
            .attr("d", line);

        svg.append("text")
            .attr("class", "label")
            .attr("transform", "rotate(270)")
            .attr("text-anchor", "middle")
            .attr("dx", -height/2)
            .attr("dy", -margin.left +20);

    },


    receiveData: function(data) {

        var name = data[0].name;
        var ylabel = data[0].ylabel;
        var url = data[0].url;

        data = data[0].data;
        // Set the title
        $("#rrd3 h1").html('RRd3: <a href="http://'+ url +'">'+ url +'</a> - '+name);

        // Set the Y scale
        y.domain([0, d3.max(d3.values(data))])
        yAxis.scale(y);
        svg.select(".y.axis")
            .transition()
            .duration(1000)
            .call(yAxis)

        // Set the X Scale
        x.domain([d3.min(d3.keys(data)), d3.max(d3.keys(data))])
        xAxis.scale(x);
        svg.select(".x.axis")
            .transition()
            .duration(1000)
            .call(xAxis)

        // Bind data to the line
        svg.selectAll(".line")
            .data(function() { return([d3.entries(data)]); })
            .transition()
            .duration(1000)
            .attr("d", line) ;
        svg.selectAll(".area")
            .data(function() { return([d3.entries(data)]); })
            .transition()
            .duration(1000)
            .attr("d", area);

        svg.selectAll('.label')
            .text(ylabel)


     }

}
