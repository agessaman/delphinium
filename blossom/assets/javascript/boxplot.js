//TOOLTIP
var div = d3.select("body").append("div")
            .attr("class", "tooltip")
            .style("opacity", 0);
    
//initialize the dimensions
var margin = {top: 5, right: 5, bottom: 5, left: 5},
    width = 400 - margin.left - margin.right,
    height = 60 - margin.top - margin.bottom,
    padding = 5,
    midline = (height - padding) / 2;
    
    
//initialize the x scale
var xScale = d3.scale.linear()
        .domain([0, max])
        .range([padding, width - padding]);  

      
var svg = d3.selectAll(".boxplot");
//draw verical line for lowerWhisker
  svg.append("line")
     .attr("class", "whisker")
     .attr("x1", xScale(min))
     .attr("x2", xScale(min))
     .attr("stroke", "black")
     .attr("y1", midline - 10)
     .attr("y2", midline + 10)
     .on("mouseover", function (d) {
        addTooltip("Min: "+min, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
     })
     .on("mouseout", function (d) {
        removeTooltip();
     });

  //draw vertical line for upperWhisker
  svg.append("line")  
     .attr("class", "whisker")
     .attr("x1", xScale(max))
     .attr("x2", xScale(max))
     .attr("stroke", "black")
     .attr("y1", midline - 10)
     .attr("y2", midline + 10)
     .on("mouseover", function (d) {
        addTooltip("Max: "+max, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
     })
     .on("mouseout", function (d) {
        removeTooltip();
     });

  //draw horizontal line from lowerWhisker to upperWhisker
  svg.append("line")
     .attr("class", "whisker")
     .attr("x1",  xScale(min))
     .attr("x2",  xScale(max))
     .attr("stroke", "black")
     .attr("y1", midline)
     .attr("y2", midline);


//IQR
  svg.append("rect")    
     .attr("class", "box")
     .attr("stroke", "black")
     .attr("fill", "white")
     .attr("x", xScale(first_quartile))
     .attr("y", midline-10)
     .attr("width", xScale(28))
     .attr("height", 20)
     .on("mouseover", function (d) {
        addTooltip("Q1: "+first_quartile+"; Q3: "+ third_quartile, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
     })
     .on("mouseout", function (d) {
        removeTooltip();
     });

    //add a small rect showing how the user did
    svg.append("rect")    
     .attr("class", "box")
     .attr("stroke", "red")
     .attr("fill", "white")
     .attr("x", xScale(item_score))
     .attr("y", midline-5)
     .attr("rx", 3)
     .attr("ry", 3)
     .attr("fill", "red")
     .attr("width", 10)
     .attr("height", 10)
     .on("mouseover", function (d) {
        addTooltip("You: "+item_score, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
     })
     .on("mouseout", function (d) {
        removeTooltip();
     });
  //draw vertical line at median
  svg.append("line")
     .attr("class", "median")
     .attr("stroke", "black")
     .attr("x1", xScale(median))
     .attr("x2", xScale(median))
     .attr("y1", midline - 10)
     .attr("y2", midline + 10)
    .on("mouseover", function (d) {
        addTooltip("Median: "+median, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
     })
     .on("mouseout", function (d) {
        removeTooltip();
     });
    

function addTooltip(text,x,y)
{
    console.log(x+","+y);
    div.transition()
            .duration(200)
            .style("opacity", .9);
    div.html(text)
            .style("left", x+ "px")
            .style("top", y+ "px");
}

function removeTooltip()
{
    div.transition()
            .duration(500)
            .style("opacity", 0);
}