console.log(min);
console.log(max);
console.log(median);
console.log(first_quartile);
console.log(third_quartile);

//initialize the dimensions
var margin = {top: 5, right: 5, bottom: 5, left: 5},
    width = 80 - margin.left - margin.right,
    height = 30 - margin.top - margin.bottom,
    padding = 10,
    midline = (height - padding) / 2;
    
//initialize the x scale
var xScale = d3.scale.linear()
               .range([padding, width - padding]);  

//initialize the x axis
var xAxis = d3.svg.axis()
              .scale(xScale)
              .orient("bottom");
      
svg = d3.select("#boxplot");
//draw verical line for lowerWhisker
  svg.append("line")
     .attr("class", "whisker")
     .attr("x1", xScale(min))
     .attr("x2", xScale(min))
     .attr("stroke", "black")
     .attr("y1", midline - 10)
     .attr("y2", midline + 10);

  //draw vertical line for upperWhisker
  svg.append("line")  
     .attr("class", "whisker")
     .attr("x1", xScale(max))
     .attr("x2", xScale(max))
     .attr("stroke", "black")
     .attr("y1", midline - 10)
     .attr("y2", midline + 10);

  //draw horizontal line from lowerWhisker to upperWhisker
  svg.append("line")
     .attr("class", "whisker")
     .attr("x1",  xScale(min))
     .attr("x2",  xScale(min))
     .attr("stroke", "black")
     .attr("y1", midline)
     .attr("y2", midline);

//  //draw rect for iqr
//  svg.append("rect")    
//     .attr("class", "box")
//     .attr("stroke", "black")
//     .attr("fill", "white")
//     .attr("x", xScale(q1Val))
//     .attr("y", padding)
//     .attr("width", xScale(iqr) - padding)
//     .attr("height", 20);

  //draw vertical line at median
  svg.append("line")
     .attr("class", "median")
     .attr("stroke", "black")
     .attr("x1", xScale(median))
     .attr("x2", xScale(median))
     .attr("y1", midline - 10)
     .attr("y2", midline + 10);
     