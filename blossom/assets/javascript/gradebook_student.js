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

function Student() 
{
    this.__init.call(this);
};

Student.prototype = {

    options: {},

    __init: function () 
    {
        this.domFunctions();
        this.get();
        //this.promise();
        this._options();
    },

    _options: function() {

        this.options.windowData = '';
        Object.freeze(this.options);

    },

    get: function() {

    },

    promise: function() {
        var promise = $.get("gradebook/getAllStudentSubmissions");
        promise.then(function (data1, textStatus, jqXHR) {
            submissions = data1;
            var inputs = document.getElementsByClassName('checkboxMultiselect');
            for(var i = 0; i < inputs.length; i++) {
                inputs[i].disabled = false;
            }
            d3.select("#chart").style("opacity","1");
        })
        .fail(function (data2) {
            console.log("Unable to retrieve student submissions");
        });
        this.callStudentsMilestoneInfo(students);
    },

    callStudentsMilestoneInfo: function (studentsArr) {
        var self = this;
        d3.select("#gridContainer").style("display","block");
        var idsArr =[];
        for(var i = 0; i <= studentsArr.length - 1; i++)
        {
            var currentStudent = studentsArr[i];
            idsArr.push(currentStudent.user_id);
        }

        if(idsArr.length > 0)
        {
            $.each(idsArr,function (d, i)
            {
               var num = parseInt(d);
               self.checkedBox(num);
            });
        }
    },

    checkedBox: function (id, slideDays)
    {
        var masterArr = submissions.filter(function (d) {
            return d.id === id;
        }),
            min_max = getCHartDragPoints(),
            dragDate = getChartDate();
        if (masterArr.length > 0)
        {
            if(slideDays){
                var masterItems = masterArr[0].items,
                    newData = [];
                $.each(masterItems,function(k,v){
                    if(v.points >= min_max[0] && v.points <= min_max[1]) {
                        if(Date.parse(v.date) <= parseInt(dragDate) || v.date <= parseInt(dragDate)){
                            newData.push(v);
                        }
                    }
                });
                var parsedData = parseDates(newData);
                addLine(parsedData, "steelblue", masterArr[0].id);
            } else {
                var masterItems = masterArr[0].items;
                var show_student_line = false;
                var maxPoint = masterItems[masterItems.length-1].points;
                var maxDate = masterItems[masterItems.length-1].date;
                if(maxPoint >= min_max[0] && maxPoint <= min_max[1]) {
                    if(Date.parse(maxDate) <= parseInt(dragDate) || maxDate <= parseInt(dragDate)){
                        show_student_line = true;
                    }
                }
                var parsedData = (show_student_line) ? parseDates(masterItems) : parseDates([]);
                addLine(parsedData, "steelblue", masterArr[0].id);
            }
        }
    },

    domFunctions: function () 
    {
        var self = this;
        $.ajax({
            url: 'gradebook/getStudentSubmission',
            success: function (data) {
                console.log(data);
            }
        });

        $(document).ready(function () {
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
                self.addTooltipGrade(str, event);
            })
            .on("mouseout", function (d) {
                self.removeTooltipGrade();
            });
        });

        //to save the last active tab after page reload
        var tabIndex = self.getStorage('tab');
        if(tabIndex == null)
        {
            tabIndex = 0;
        }
        $('.grade-tabs li').eq(tabIndex).addClass('active');
        $('.tab-pane').eq(tabIndex).addClass('active');
        $(document).on('click','.grade-tabs li',function(){
            self.setStorage('tab', $(this).index());
        });

    },

    addTooltipGrade: function(text, event)
    {
        div.transition()
            .duration(200)
            .style("opacity", .9);
        div.html(text)
            .style("left", (d3.event.pageX) + "px")
            .style("top", (d3.event.pageY - 28) + "px");
    },

    removeTooltipGrade: function()
    {
        div.transition()
            .duration(500)
            .style("opacity", 0);
    },

    getStorage: function(key) 
    {
        return localStorage.getItem(key);
    },

    setStorage: function(key,args)
    {
        localStorage.setItem(key,args);
    }

}

var StudentClass = new Student();