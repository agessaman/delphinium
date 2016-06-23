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
    calculateDates();
    scaleTimer();
    drawTimer();
});

function calculateDates(){
    startDate = new Date(start);
    start = startDate.getFullYear()+"-"+(startDate.getMonth()+1)+"-"+startDate.getDate()+" "+
        startDate.getHours()+":"+startDate.getMinutes()+":"+startDate.getSeconds();

    endDate = new Date(end);
    end = endDate.getFullYear()+"-"+(endDate.getMonth()+1)+"-"+endDate.getDate()+" "+
        endDate.getHours()+":"+endDate.getMinutes()+":"+endDate.getSeconds();
}

function scaleTimer(){

}

function drawTimer(){
    var width = 700;    //define clock width
    var height = 200;    //define clock height
    var pi2 = 2 * Math.PI;
    var radiusIn = 60;
    var radiusOut = 90;

    var svg = d3.select("#clock").append("svg")
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("transform", "translate(" + width/2 + "," + height/2 + ")");

    function createDate(strDate) {
        var format = d3.time.format("%Y-%m-%d %H:%M:%S");
        return format.parse(strDate);
    }

    function newArc(name, inner, outer, position, label) {
        var arc = d3.svg.arc()
            .startAngle(0)
            .endAngle(pi2)
            .innerRadius(inner)
            .outerRadius(outer);        
        
        var meter = svg.append("g")
           .attr("class","progress-meter")
           .attr("transform", "translate(" + (position * 2.5 * outer) + ",0)");
        
        var text = meter.append("text")
           .attr("text-anchor", "middle")
           .attr("dy", "0.25em")
           .attr("class","count text");
        
        var label = meter.append("text")
           .attr("text-anchor", "middle")
           .attr("dy", "2.5em")
           .attr("class","label text")
           .text(label);
        
        var shadow = meter.append("path")
            .attr("class", "shadow")
            .attr("transform", "translate(0, 5)");
        
        var foreground = meter.append("path")
            .attr("class", "foreground"+name);
        
        return [foreground, shadow, arc, text, label];
    }
     
    function updateArc(arc, value, target, label, labels) {
        if (value === target)
            value = 0;
        var arcVal = pi2;
        if(target!==0)//don't divide by zero
        {
            arcVal = ((target - value) * pi2 / target);
        }
        arc[0].attr("d", arc[2].startAngle(arcVal));
        arc[1].attr("d", arc[2].startAngle(arcVal));
        arc[3].text(value);
        if (value!==1) 
            arc[4].text(labels);
        else
            arc[4].text(label);
    }

    function remainingDays(current, target) {
        return Math.floor((target - current) / 1000 / 60 / 60 / 24);
    }

    function remainingHours(current, target) {
        return Math.floor((target - current) / 1000 / 60 / 60) % 24;
    }

    function remainingMinutes(current, target) {
        return Math.floor((target - current) / 1000 / 60) % 60;
    }

    function remainingSeconds(current, target) {
        return Math.floor((target - current) / 1000) % 60;
    }

    function createClock(start, end, width, height, radiusIn, radiusOut) {
        
        var day = newArc("Day", radiusIn, radiusOut, -1, "DAYS");
        var hour = newArc("Hour", radiusIn, radiusOut, 0, "HRS");
        var minute = newArc("Minute", radiusIn, radiusOut, 1, "MIN"); 

        var textg = svg.append("g")
            .attr("transform", "translate(0," + height/3 + ")");
        
        var text = textg.append("text")
           .attr("text-anchor", "middle")
           .attr("class","details text");
        
        var flash = true;
        function updateClock()
        {
            var timeNow = new Date();

            var rDays = remainingDays(timeNow, end);
            var rHours = remainingHours(timeNow, end);
            var rMinutes = remainingMinutes(timeNow, end);
            
            if (rDays < 0)
                rDays = 0;
            if (rHours < 0)
                rHours = 0;
            if (rMinutes < 0)
                rMinutes = 0;
            
            if((rDays===0) && (rHours ===0) && (rMinutes ===0))
            {
                clearInterval(interval);
            }
            
            updateArc(day, rDays, remainingDays(start, end), "DAY", "DAYS");
            updateArc(hour, rHours, 24, "HR", "HRS");
            updateArc(minute, rMinutes, 60, "MIN", "MIN");
            
            document.getElementById("clockText").innerHTML = rDays + " Day" + (rDays != 1?"s ":" ") + rHours + " Hour" + (rHours != 1?"s ":" ") + rMinutes + " minute" + (rMinutes != 1?"s ":" ") + "till course ends";
            if(flash){
                if(rDays<=10){
                    var intervals = [];
                    intervals.push(setInterval(function(){
                        document.getElementById("clock").style.backgroundColor = "red";
                    },5));
                    intervals.push(setInterval(function(){
                        document.getElementById("clock").style.backgroundColor = "#089fd7";
                    },7));

                    setTimeout(function(){
                        intervals.forEach(function(id){ clearInterval(id); });
                        document.getElementById("clock").style.backgroundColor = "#089fd7";
                    },1000);
                    
                    
                }
                flash=false;
            }
        }
                            
        var interval = setInterval(updateClock, 1000);
        
    }

    var startDate = createDate(start); //define start date for clock
    var endDate = createDate(end); //define end date for 
    createClock(startDate, endDate, width, height, radiusIn, radiusOut);
}