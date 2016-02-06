//initialize the dimensions
var margin = {top: 5, right: 5, bottom: 5, left: 5},
    width = 300 - margin.left - margin.right,
    height = 40 - margin.top - margin.bottom,
    padding = 5,
    midline = (height - padding) / 2;

var promise = $.get("getStudentGradebookData");
promise.then(function (data, textStatus, jqXHR) {
        d3.select("#spinner").style("display","none");
        makeTables(data);
    })
    .fail(function (data2) {
    });


function makeTables(data)
{
    //TOOLTIP
    var div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);

    var columnsNames = ['Assignment Name', 'Score', 'Analytics'];
    var columns = ["name", "score", "analytics"];

    //delete old tables (for professor gradebook)
    d3.select("#gradebook").selectAll("table").remove();
    var table = d3.select("#gradebook")
        .selectAll("div")
        .data(data)
        .enter().append("table");
    table.attr("class", "table table-condensed table-responsive table-bordered table-hover table-striped");

    caption = table.append("caption")
        .text(function (d) {
            return d.group_name;
        });
    thead = table.append("thead"),
// append the header row
        thead.append("tr")
            .selectAll("th")
            .data(columnsNames)
            .enter()
            .append("th")
            .text(function (d) {
                return d;
            });


    tbody = table.append("tbody");

    var rows = tbody.selectAll("tr")
        .data(function (d)
        {
            return d.content;
        })
        .enter()
        .append("tr");


//     create a cell in each row for each column
    var mapping = [];
    var cells = rows.selectAll("td")
        .data(function (d) {
            return columns.map(function (column) {
                var analytics = {max_score: d['max_score'], min_score: d['min_score'], median: d['median'], first_quartile: d['first_quartile'], third_quartile: d['third_quartile'], score: d['score']};
                var name = {name: d['name'], html_url: d['html_url']};
                var score = {score: d['score'], points_possible: d['points_possible']};
                var obj = {name: name, score: score, analytics: analytics};


                mapping.push(analytics);

                return obj;
            });
        })
        .enter()
        .append("td")
        .attr("class", function (d, i)
        {
            if (i === 2)
            {
                return "boxplot";
            }
            else if (i === 1)
            {
                return "score td";
            }
            else
            {
                return "name td"
            }
        })
        .attr("id", "cell")
        .html(function (d, i) {
            if (i === 0)
            {
                return "<a href='" + d.name.html_url + "' target='_blank'>" + d.name.name + "</a>";
            }
            else if (i === 1)
            {
                var score = d.score.score !== null ? score = d.score.score : score = "--";
                return score + " / " + d.score.points_possible;
            }
            else
            {
                return writeBoxplot(d3.select(this), d.analytics);
            }

        });

}


function writeBoxplot(td, analytics)
{
    var string = "";
    var xScale = d3.scale.linear()
        .domain([0, analytics.max_score])
        .range([padding, width - padding]);
    var score = analytics.score !== null ? score = xScale(analytics.score) : score = "--";

    if(analytics.max_score === null)
    {
        string = "--";
        return string;
    }

    var minscore = xScale(analytics.min_score);
    var maxscore = xScale(analytics.max_score);
    var midlineup = midline + 7.5;
    var midlinedown = midline - 7.5;
    var firstQ = xScale(analytics.first_quartile);
    var median = xScale(analytics.median);
    var medianMinusFirstQ = xScale(analytics.median - analytics.first_quartile);
    var thirdQMinusMedian = xScale(analytics.third_quartile - analytics.median);
    var jsonAnalytics = (JSON.stringify(analytics));

    string = "<svg width='300' height='30' onmouseenter='addTooltip(event,300," + jsonAnalytics + ");' onmouseleave='removeTooltip();'>";
    string += "<line class='min whisker' x1='" + minscore + "' x2='" + minscore + "' y1='" + midlinedown + "' y2='" + midlineup + "' ></line>";
    string += "<line class='max whisker' x1='" + maxscore + "' x2='" + maxscore + "' y1='" + midlinedown + "' y2='" + midlineup + "' ></line>";
    string += "<line class='whisker' x1='" + minscore + "' x2='" + maxscore + "' y1='" + midline + "' y2='" + midline + "'></line>";

    var height = midlineup- midlinedown;
    //First IQR
    string += "<rect class='first box' x='" + firstQ + "' y='" + midlinedown + "' height='"+height+"' width='" + medianMinusFirstQ + "'></rect>";
    //Second IQR
    string += "<rect class='third box' x='" + median + "' y='" + midlinedown + "' height='"+height+"' width='" + thirdQMinusMedian + "'></rect>";

    if (score !== "--")
    {

        var scoreMinusOne = score - 3;
        newmidlinedown = midline - 3;
        //user
        string += "<rect class='studentScore' x='" + scoreMinusOne + "' y='" + newmidlinedown + "' height='6' width='6' rx='3' ry='3'></rect>";

    }
    //median
    string += "<line class='whisker' x1='" + median + "' x2='" + median + "' y1='" + midlinedown + "' y2='" + midlineup + "'></line>";
    string += "</svg>";

    return string;
}

function addTooltip(event, width,analytics)
{
    var text = "Min score: " + analytics.min_score + "\n";
    text += "<br/>";
    text += "Median: " + analytics.median + "\n";
    text += "<br/>";
    text += "Max score: " + analytics.max_score + "\n";
    text += "<br/>";
    text += "First Q: " + analytics.first_quartile + "\n";
    text += "<br/>";
    text += "Third Q: " + analytics.third_quartile + "\n";

    var x = event.pageX;
    var y = event.pageY;
    var windowWidth = $(window).innerWidth();
    if((x+width)>windowWidth)
    {
        var over = (x+width) - windowWidth;
        x = x-over;
    }

    var div = d3.select(".tooltip");
    div.transition()
        .duration(200)
        .style("opacity", .9);
    div.html(text)
        .style("left", (x) + "px")
        .style("top", (y) + "px");
}

function removeTooltip()
{
    var div = d3.select(".tooltip");
    div.transition()
        .duration(500)
        .style("opacity", 0);
}
