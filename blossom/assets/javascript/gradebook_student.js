/*
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

$(document).ready(function(){
    div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);

    d3.select("#iGradeTooltip").on("mouseover", function (d) {

            var str = "<table class='table table-condensed table-gradingScheme'><thead> <tr> <th>Points</th> <th>Letter Grade</th></tr> </thead> <tbody> ";
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