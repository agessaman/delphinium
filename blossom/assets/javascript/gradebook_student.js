$(document).ready(function(){
    div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);

    d3.select("#iGradeTooltip").on("mouseover", function (d) {

            var str = "<table class='table table-condensed table-gradingScheme'><thead> <tr> <th>Value</th> <th>Letter Grade</th></tr> </thead> <tbody> ";
            for(var i=0;i<=gradingScheme.length-1;i++)
            {
                var item = gradingScheme[i];

                str+="<tr><td>"+item.value+"</td> <td>"+item.name+"</td> </tr>";
            }
            str+="</tbody> </table>";
            addTooltipGrade(str, event);
        })
        .on("mouseout", function (d) {
            removeTooltipGrade();
        });
});


function addTooltipGrade(text, event)
{
    div.transition()
        .duration(200)
        .style("opacity", .9);
    div.html(text)
        .style("left", (d3.event.pageX) + "px")
        .style("top", (d3.event.pageY - 28) + "px");
}


function removeTooltipGrade()
{
    div.transition()
        .duration(500)
        .style("opacity", 0);
}