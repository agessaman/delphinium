
//initialize the dimensions
var margin = {top: 5, right: 5, bottom: 5, left: 5},
    width = 400 - margin.left - margin.right,
    height = 60 - margin.top - margin.bottom,
    padding = 5,
    midline = (height - padding) / 2;

//TOOLTIP
var div = d3.select("body").append("div")
            .attr("class", "tooltip")
            .style("opacity", 0);
        
var columnsNames  = ['Assignment Name','Score','Analytics'];
var columns = ["name", "score","analytics"];
var table = d3.select("#gradebook")
        .selectAll("div")
        .data(data)
        .enter().append("table");
table.attr("class","table table-condensed table-responsive table-bordered table-hover");

caption = table.append("caption")
        .text(function(d){
            return d.group_name;
        });
thead = table.append("thead"),
// append the header row
thead.append("tr")
    .selectAll("th")
    .data(columnsNames)
    .enter()
    .append("th")
        .text(function(d) { return d; });


tbody = table.append("tbody");

var rows = tbody.selectAll("tr")
        .data(function(d)
        {
            return d.content;
        })
        .enter()
        .append("tr");


//     create a cell in each row for each column
    var mapping=[];
    var cells = rows.selectAll("td")
        .data(function(d) {
            return columns.map(function(column) {
                var analytics = {max_score:d['max_score'],min_score:d['min_score'],median:d['median'],first_quartile:d['first_quartile'],third_quartile:d['third_quartile'],score:d['score']};
                var name = {name:d['name'],html_url:d['html_url']};
                var score = {score:d['score'],points_possible:d['points_possible']};
                var obj = {name:name,score:score,analytics:analytics};
                
                
                mapping.push(analytics);
                return obj;
            });
        })
        .enter()
        .append("td")
        .attr("style", "font-family: Courier") // sets the font style
        .attr("class",function(d,i)
        {
            
            if(i===2)
            {
                return "boxplot";
            }
            else
            {
                return "td";
            }
        })
        .attr("id","cell")
        .html(function(d,i) {
            if(i===0)
            {
                return "<a href='"+d.name.html_url+"'>"+d.name.name+"</a>";
            }
            else if(i===1)
            {
                var score = d.score.score!==null?score = d.score.score:score = "--";
                return score+" / "+d.score.points_possible;
            }
            else
            {
                return "";
            }
               
        });
        
        cells
            .selectAll("svg")
            .data(mapping)
            .enter()
            .append(function(d)
            {
                if(this.className==="boxplot")
                {
                    return document.createElement("svg");
                }
                else
                {
                    return document.createElement('span');
                }
            });
//        var svg = d3.selectAll(".boxplot").selectAll("svg")
            
    
    //initialize the x scale
//    var xScale = d3.scale.linear()
//            .domain([0, 40])
//            .range([padding, width - padding]);  


    //draw verical line for lowerWhisker
//      svg.append("svg:line")
//         .attr("class", "whisker")
//         .attr("x1", 4)// xScale(mapping.min_score))
//         .attr("x2", 4)//xScale(mapping.min_score))
//         .attr("stroke", "black")
//         .attr("y1", midline - 10)
//         .attr("y2", midline + 10)
//         .on("mouseover", function (d) {
////            addTooltip("Min: "+mapping.min_score, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
//         })
//         .on("mouseout", function (d) {
//            removeTooltip();
//         });
//
//      //draw vertical line for upperWhisker
//      svg.append("svg:line")  
//         .attr("class", "whisker")
//         .attr("x1", 18)//xScale(mapping.max_score))
//         .attr("x2", 18)//xScale(mapping.max_score))
//         .attr("stroke", "black")
//         .attr("y1", midline - 10)
//         .attr("y2", midline + 10)
//         .on("mouseover", function (d) {
////            addTooltip("Max: "+mapping.max_score, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
//         })
//         .on("mouseout", function (d) {
//            removeTooltip();
//         });
//
//      //draw horizontal line from lowerWhisker to upperWhisker
//      svg.append("svg:line")
//         .attr("class", "whisker")
//         .attr("x1",  4)//xScale(mapping.min_score))
//         .attr("x2",  18)//xScale(mapping.max_score))
//         .attr("stroke", "black")
//         .attr("y1", midline)
//         .attr("y2", midline);
//
//
//    //IQR
//      svg.append("svg:rect")    
//         .attr("class", "box")
//         .attr("stroke", "black")
//         .attr("fill", "white")
//         .attr("x", 4)//xScale(mapping.first_quartile))
//         .attr("y", midline-10)
//         .attr("width", 28)//xScale(28))
//         .attr("height", 20)
//         .on("mouseover", function (d) {
////            addTooltip("Q1: "+mapping.first_quartile+"; Q3: "+ mapping.third_quartile, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
//         })
//         .on("mouseout", function (d) {
////            removeTooltip();
//         });
//
//        //add a small rect showing how the user did
//        svg.append("svg:rect")    
//         .attr("class", "box")
//         .attr("stroke", "red")
//         .attr("fill", "white")
//         .attr("x", 16)//xScale(mapping.score))
//         .attr("y", midline-5)
//         .attr("rx", 3)
//         .attr("ry", 3)
//         .attr("fill", "red")
//         .attr("width", 10)
//         .attr("height", 10)
//         .on("mouseover", function (d) {
////            addTooltip("You: "+mapping.score, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
//         })
//         .on("mouseout", function (d) {
//            removeTooltip();
//         });
//      //draw vertical line at median
//      svg.append("svg:line")
//         .attr("class", "median")
//         .attr("stroke", "black")
//         .attr("x1", 10)//xScale(mapping.median))
//         .attr("x2", 10)//xScale(mapping.median))
//         .attr("y1", midline - 10)
//         .attr("y2", midline + 10)
//        .on("mouseover", function (d) {
////            addTooltip("Median: "+mapping.median, d3.select(this).attr("x1"), d3.select(this).attr("y1"));   
//         })
//         .on("mouseout", function (d) {
//            removeTooltip();
//         });


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

    
    
    
//console.log(contentArr);


// render the table
//var peopleTable = tabulate(data, ["name", "html_url","points_possible","score","max_score","min_score","median","first_quartile","third_quartile"]);
//
//// uppercase the column headers
//peopleTable.selectAll("thead th")
//    .text(function(column) {
//        return column.charAt(0).toUpperCase() + column.substr(1);
//    });
//    
// sort by age
//peopleTable.selectAll("tbody tr")
//    .sort(function(a, b) {
//        return d3.descending(a.age, b.age);
//    });
//    
//function tabulate(data, columns) {
//    var table = d3.select("#container").append("table"),
//        thead = table.append("thead"),
//        tbody = table.append("tbody");
//
//    // append the header row
//    thead.append("tr")
//        .selectAll("th")
//        .data(columns)
//        .enter()
//        .append("th")
//            .text(function(column) { return column; });
//
//    // create a row for each object in the data
//    var rows = tbody.selectAll("tr")
//        .data(data)
//        .enter()
//        .append("tr");
//
//    // create a cell in each row for each column
//    var cells = rows.selectAll("td")
//        .data(function(row) {
//            return columns.map(function(column) {
//                return {column: column, value: row[column]};
//            });
//        })
//        .enter()
//        .append("td")
//            .text(function(d) { return d.value; });
//    
//    return table;
//}






//
////var columnsNames  = ['Assignment Name','Score','Analytics'];
//
//var table = d3.select("#gradebook")
//        .selectAll("div")
//        .data(data)
//        .enter().append("table");
//table.attr("class","table table-condensed table-responsive table-bordered table-hover");
//
//caption = table.append("caption")
//        .text(function(d){
//            return d.group_name;
//        });
//thead = table.append("thead"),
//// append the header row
//thead.append("tr")
//    .selectAll("th")
//    .data(columnsNames)
//    .enter()
//    .append("th")
//        .text(function(d) { return d; });
//
//tbody = table.append("tbody");
//
//var rows = tbody.selectAll("tr")
//        .data(function(d)
//        {
//            return d.content;
//        })
//        .enter()
//        .append("tr");
//
//var cells = rows.selectAll("td")
//        .data(function (d)
//        {
//            console.log(d)
//        })
//        .enter()
//        .append("td");
//
//    // create a cell in each row for each column
////    var cells = rows.selectAll("td")
////        .data(function(d) {
////            return d.content;
//////            console.log(d.content);
//////            return columns.map(function(column) {
//////                return {column: column, value: row[column]};
//////            });
////        })
////        .enter()
////        .data(function(d, i) { 
////            return d; 
////})
////        .append("td")
////        .attr("style", "font-family: Courier") // sets the font style
////            .html(function(d) { return d.value; });
//    
//
////g.selectAll("scatter-dots")
////            .data(bonus_penalties)
////            .enter().append("svg:rect")
////            .attr("x", function (d) {
////                return (0);
////            })
////            .attr("width", function (d) {
////                return (185);
////            })
////            .attr("y", function (d) {
////                return encouragementAxisScale(d.points);
////            })
////            .attr("height", function (d) {
////                return 0.5;
////            })
////            .attr("stroke-width", 0.5)
////            .attr("stroke", "lightgray")
////            .on("mouseover", function (d) {
////                var date = new Date(d.due_at.date);
////                addTooltip(d.points + " pts due " + date.toDateString() + " at " + date.toLocaleTimeString())
////            })
////            .on("mouseout", function (d) {
////                removeTooltip();
////            });