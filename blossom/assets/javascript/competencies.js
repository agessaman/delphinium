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

/* competencies.js
    data & details are setup by View partials
    
    Uses:
    submissions for tags & scores
    assignments for points_possible
	
	Stem:
	assignments must contain special tags starting with C:
*/

//$(document).ready(function() {
	
    var div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);
    
    //console.log(assignments.length, assignments);//has points_possible
    //console.log(submissions.length, submissions);//has all tags & score
    // related by assignment_id

	/* Delphinium options */
	$('#popinfo').popover();// activate info
	
    /* if Learner, filterData then showCompetencies
        else 
        create assignments using Module Items
        showCompetencies data, details
        plus configure Settings
    */
    if(role == 'Learner') {
		/* set up the popover content text */
		$('#popinfo').attr("data-content","Click a bar to view competency details.");
		
        filterData();
        showCompetencies();
		
    } else {
		/* set up the popover text for instructor */
		$('#popinfo').attr("data-content","Instructor view does not contain submissions. Click a bar to view competency details.");
	
		/* set id,course in the POST they are not editable in the form
			Add hidden input fields so they will transfer to onUpdate
			if fields are set to hidden: true, they do not appear in the post
		*/
		
		$('#Form-outsideTabs').append('<input type="hidden" name="Competencies[id]" value="'+config.id+'" /> ');
		$('#Form-outsideTabs').append('<input type="hidden" name="Competencies[course_id]" value="'+config.course_id+'" /> ');
		
		// Fix Animate checkbox switch
		//$('<div style="height:90px;" class="clearfix"></div>').insertBefore('.checkbox-field').parent;
		//$('.checkbox-field').attr('style','margin-left:20px').removeClass('span-right').addClass('span-left');
		// Hide the name field so instructor cant change it
		$('#Form-field-Competencies-Name-group').hide();
		
		// Set the color picker to current color
		$('div #ColorPicker-formColor-input-Color').val(config.Color);
		console.log('instance: '+config.id,config.Name,config.Size,config.Color,config.Animate,config.course_id);
		
		$('#cog').on('click', function(e){
			$('#competence-configuration').modal('show');
		});
		
        filterModuleTags();
        showCompetencies();
    }

    function filterModuleTags() {

        //var modules={{modules|raw}};// twig inside instructorView use module items to build data
        //console.log(modules.length, modules);
        // replace assignments with modAssignments that have tags
        assignments=[];
        var modAssignments = [];
        var tagList=[];
        for(var m=0; m<modules.length; m++) {

            for(var mi=0; mi<modules[m].items.length; mi++) {

                var asgn = modules[m].items[mi].content[0];
                // if assignment with tags add to array
                if(asgn != undefined) {

                    var temp = asgn['tags'].split(', ');
                    for(var t=0; t<temp.length; t++) {
                        var atag = temp[t].substring(0,2).toLowerCase();
                        if(atag != 'c:') {
                            // console.log('PAGE FAQ HAS tag pre !'); 
                           temp.splice(t,1); 
                        } else {
                            //console.log(tagarray[t]); // some undefined slip through
                            //Construct a list of unique tags for sorting competency groups
                            if(tagList.indexOf(temp[t]) == -1 && temp[t] != undefined){
                                tagList.push(temp[t]);
                            }
                        }
                    }
                    // remove unused tags from assignment
                    // if any tags are left
                    if(temp.length > 0) {
                        modules[m].items[mi].content[0]["tags"]=temp.join();
                        // add module id,title,locked,url it belongs to for details
                        //modules[m].items[mi].content[0]["module_id"]=modules[m].id;
                        //modules[m].items[mi].content[0]["name"]=modules[m].title;
                        modules[m].items[mi].content[0]["locked"]=modules[m].locked;
                        //for click bar modal assignments detail

                        modules[m].items[mi].content[0]["name"]=modules[m].items[mi].title;
                        modules[m].items[mi].content[0]["html_url"]=modules[m].items[mi].url;
                        modules[m].items[mi].content[0]["assignment_id"]=modules[m].items[mi].content_id;
                        modules[m].items[mi].content[0]["id"]=modules[m].items[mi].content_id;

                        //module_item_id?
                        modAssignments.push(modules[m].items[mi].content[0]);
                        assignments.push(modules[m].items[mi].content[0]);
						//console.log('item:',modules[m].items[mi].content[0]);
                    }
                }
            }
        }
        console.log('tagList:', tagList.length, tagList);
        //console.log('modAssignments:', modAssignments.length, modAssignments);
        //console.log('assignments:',assignments.length, assignments);

        var gTotal=0;// total points
        var gAmount=0;//remains 0, no submissions

        for(var l=0; l<tagList.length; l++) {
            var name = tagList[l].substring(2,tagList[l].length);
            details.push({"name":name,"assignments":[]});
            // group submissions by tag
            var group = $.grep(modAssignments, function(elem, indx){
                if(hasTag(elem, tagList[l])) { return elem; }
            });

            //console.log('group '+l, tagList[l], group.length, group);
            gTotal=0;// reset for each group

            // for each group of assignments
            for(var g=0; g<group.length; g++) {

                if(group[g].points_possible){
                    // add up points possible for all assignments with this tag
                    gTotal += group[g].points_possible;
                    // for Instructor click bar
                    details[l]['assignments'].push(group[g]);
                }
            }

            // construct data for D3
            // xcale((data[i].percent/100)*maxTotal)
            var percent = Math.round(gAmount/gTotal*100);
            data.push({"name":name,"total":gTotal,"amount":gAmount,"percent":percent});
        }
        //console.log('data:',data.length,data);
        //console.log('details:',details.length,details);
    }/* END filterModuleTags */

    function filterData() {
        console.log('submissions:',submissions);
        console.log('assignments:',assignments);
        //find all submissions that have tags
        var tagged =$.grep(submissions, function(elem, indx){
            return elem['tags'] != "";
        });
        //console.log(tagged.length, tagged);

        var tagList=[];
        for(i=0; i<tagged.length; i++) {
            //remove tags that do not start with 'C:'
            var tagarray = tagged[i]['tags'].split(', ');
            for(var t=0; t<tagarray.length; t++) {
                var atag = tagarray[t].substring(0,2)
                if(atag != 'C:' && atag != 'c:') {
                    tagarray.splice(t,1);
                }
                //console.log(tagarray[t]); // some undefined slip through
                //Construct a list of unique tags for sorting
                if(tagList.indexOf(tagarray[t]) == -1 && tagarray[t] != undefined){
                    tagList.push(tagarray[t]);
                }
            }
            // remove any non C:ompetency tags
            tagged[i]['tags']=tagarray.join();
            var tdetails = 'subm['+i+']';
                tdetails+= ' tags:'+tagged[i].tags+' [score:'+tagged[i].score+']<br/>';
        }
        console.log(tagList.length, 'tagList:'+tagList);

        /*
        loop thru tagList to sort tagged submissions into groups, 
        for each submission in each group add up amount

        add up total Points from points_possible in each assignments that match each group assignment_id
        assignments do NOT have Tags: submissions do NOT have points_possible
        */
        //var details=[];// for modal '#detailed' body content
        //var data =[];// json for d3
        var gTotal=0, gAmount=0;
        for(var l=0; l<tagList.length; l++) {
            // {"name":
            var name = tagList[l].substring(2,tagList[l].length);
            details.push({"name":name,"assignments":[]});
            // group by tag
            var group = $.grep(tagged, function(elem, indx){
                if(hasTag(elem, tagList[l])) { return elem; }
            });
            //console.log(group.length, group);
            // for each group
            for(var g=0; g<group.length; g++) {
                gAmount += group[g].score;
                //add up scores and points possible
                for(var a=0; a<assignments.length; a++) {
                    if(assignments[a].assignment_id == group[g].assignment_id) {
                        if(assignments[a].points_possible){
                            gTotal += assignments[a].points_possible;
                            // add matching assignments ids for modal view
                            details[l]['assignments'].push({'id':assignments[a].assignment_id});
                        }
                    }
                }    
            }
            // construct data for D3 // xcale((data[i].percent/100)*maxTotal)
            var percent = Math.round(gAmount/gTotal*100);
            data.push({"name":name,"total":gTotal,"amount":gAmount,"percent":percent});
        }
        //console.log(data.length,data);
        //console.log(details.length,details);
    }/* END filterData */

    /* *******************************************
        check if object has tag needed
        used from filterData & filterModuleTags
    */
    function hasTag(obj,tag) {
        var tagarray = obj['tags'].split(',');
        var marked=false;
        //console.log(tagarray);//["C:Ideas", "C:Align", "C:People"]
        for(var t=0; t<tagarray.length; t++) {
            if(tagarray[t].toLowerCase() == tag.toLowerCase()) { marked=true; }
        }
        return marked;
    }

    function showCompetencies() {
        var big=[];
        for(i=0; i<data.length; i++) {
            big.push(data[i].total);
        }
        //find largest total for d3.scale
        var maxTotal=Math.max.apply(null,big);
        //console.log('maxTotal:'+maxTotal);// maxTotal: -Infinity
        var grid=[20,30,40,50,60,70,80,90];//vertical % tick marks

        // NOW D3 it!
        var competenciesView = d3.selectAll(".competenciesView");
        var competenciesSVG = d3.selectAll(".competenciesSVG");
        var rowHeight = 45;// a property?
        var competenciesWidth = 250;// could be a property?
        var competenciesHeight = data.length*rowHeight;

        //var competenciesSize=config.Size.toLowerCase();// twig in default.htm
        //console.log('competenciesSize',competenciesSize);
        if(competenciesSize == "small") {
            competenciesSVG.attr('width', competenciesWidth / 1.5)
                            .attr('height', competenciesHeight / 1.5);
            competenciesView.attr('transform', "scale(.66)");
        } else if (competenciesSize == "large") {
            competenciesSVG.attr('width', competenciesWidth * 1.5)
                            .attr('height', competenciesHeight * 1.5);
            competenciesView.attr('transform', "scale(1.5)");
        } else {
            // default
            competenciesSVG.attr('width', competenciesWidth)
                            .attr('height', competenciesHeight);
        }

        // remove preloader
        //$('#outline').removeClass('spinner');
        $('.loading-indicator-container').hide();// remove
        // Only show the d3 if data is valid
        //TEST data=[];
        if(data.length == 0 ) {
        /*
            No bars would be rendered because there are no tags to define them.
            Show a border using the Color to define the Size with instructions to setup Stem.
            use the details modal to notify user? $('.modal-body').html(content);
            $('#outline:style').css({'border': '1px solid '+competenciesColor, 'width':competenciesWidth+'px', 'height':'250px'});
            instructions to set up stem
        */
        $(".competenciesSVG").hide();// Nothing to show
            //Alternative display Set up Stem
        /*    var compview = d3.selectAll(".competenciesSVG");
                compview.attr('height', 160)
                    .append('rect')
                    .attr('x',2).attr('y', 2)
                    .attr('height', 156)
                    .attr('width', competenciesWidth-4)
                    .attr('fill', 'none')
                    .attr("stroke-width",2)
                    .attr('stroke', competenciesColor)
                compview.append('text')
                    .text('Set up Stem')
                    .attr('x',14).attr('y',80)
                    .attr('font-size', '2em')
                    .attr('fill',competenciesColor);
            */
        } else {
            // Show the component
            ////var competenciesAnimate=config.Animate;
            ////var competenciesColor=config.Color; 
            var percentColor = '#CCCCCC';// med gray or inverse amount color
            var competencies = d3.selectAll(".competenciesView");// a <g>roup
            var xcale = d3.scale.linear()
                .domain([0, maxTotal+2])
                .range([0, competenciesWidth]);
            //console.log(xcale(80), xcale(398), xcale(598));

            for (var i = 0; i < data.length; i++) {
                //competency name
                competencies.append('text')
                    .text(data[i].name)
                    .attr('font-size', '1em')
                    .attr('y', i * rowHeight + 13);

                //amount bars
                if(competenciesAnimate == true){
                    competencies.append('rect')
                            .attr('height', 18)
                            .attr('width', 0)
                            .attr('fill', 'white')
                            .attr('y', i * rowHeight + 18)
                            .transition()
                                .delay(250*i)
                                .duration(1000)
                                .attr('width', xcale((data[i].percent/100)*maxTotal))
                                .attr('fill', competenciesColor)
                                .ease('easeInQuart');//easeOutQuad bounce
                } else {
                    competencies.append('rect')
                            .attr('height', 18)
                            .attr('y', i * rowHeight + 18)
                            .attr('width', xcale((data[i].percent/100)*maxTotal))
                            .attr('fill', competenciesColor);
                }
                //grid lines 4px tall
                competencies.selectAll("gridline")
                    .data(grid)
                    .enter()
                    .append("line")
                    .attr("x1",function(d, i){ return xcale((grid[i]/100)*maxTotal); })
                    .attr("y1", i * rowHeight + 26)
                    .attr("x2",function(d, i){ return xcale((grid[i]/100)*maxTotal); })
                    .attr("y2",i * rowHeight + 30)
                    .style("stroke",'silver')
                    .style("stroke-width",1);

                //outline			
                competencies.append('rect')
                        .attr('height', 18)
                        .attr('width', xcale(maxTotal))
                        .attr('stroke-width', '2')
                        .attr('stroke', 'gray')
                        .attr('fill', 'none')
                        .attr('y', i * rowHeight + 18);

                //percent completed
                competencies.append('text')
                        .text(data[i].percent+'%')
                        .attr('fill', percentColor)
                        .attr('font-size', '0.9em')
                        .attr('x', 10)
                        .attr('y', i*rowHeight+31);

                //invisble btn on each bar for tooltip
                //also stores data[i] info
                competencies.append('rect')
                    .style('cursor','pointer')
                    .attr('height', 18)
                    .attr('width', xcale(maxTotal))
                    .attr('stroke-width', '0')
                    .attr('fill', 'white')
                    .attr('opacity',0.01)
                    .attr('y', i * rowHeight + 18)
                    .attr('class','comp')
                    .attr('id', function(d, i) { return i; })
                    .attr('data-name', function(d) { return data[i].name; })
                    .attr('data-value', function(d) { return data[i].amount+' pts. earned,<br/>'+data[i].total+' pts. possible'; })
                    .on('mouseover', function (d) {
                        //console.log(d3.event.currentTarget.id);//
                        var text = $(d3.event.currentTarget).attr('data-value');
                        addTooltip(text);
                    })
                    .on('mousemove', function(d) {
                        var tx = d3.event.clientX;
                        var ty = d3.event.clientY-38;//upper right of mouse
                        ///console.log('tx:'+tx);
                        $('.tooltip').css({'left':tx + 'px','top':ty + 'px','background':competenciesColor});
                    })
                    .on('mouseout', function (d) {
                        removeTooltip();
                    })
                    .on('click', function(d) {
                        div.style("opacity",0);// removeTooltip immediately
                        //console.log('clicked: '+$(d3.event.currentTarget).attr('data-name'));
                        var detailItem = $.grep(details, function(elem, indx){
                            return elem['name'] == $(d3.event.currentTarget).attr('data-name');
                        });
                        //console.log(detailItem[0]);
                        displayDetails(detailItem[0]);
                    });
            }/* End for( */
        }/* End Else */
    }/* End showCompetencies */

    //data-toggle="tooltip" title="Settings" data-placement="bottom"
    function addTooltip(text)
    {
        div.transition()
            .duration(200)
            .style("opacity", .9);
        div.html(text)
            .style("left", (d3.event.pageX) + "px")
            .style("top", (d3.event.pageY - 28) + "px");
    }

    function removeTooltip()
    {
        div.transition()
            .duration(500)
            .style("opacity", 0);
    }

    /*
        display assignments that match tag group
        display rows for details[{'name': align, assignment_id:[{#, #, #, #}]}]

        grey = locked : compare locked_at w/ current date?
        blue = done : also if score = 0
        green= available to do still

    */
    function displayDetails(item) {
        //console.log('item:',item);
        $('#detailed-title').html(item.name+' Competency Details');
        var content='';
        var locked=false;
        for(var i=0; i<item.assignments.length; i++) {
            //construct modal content
            var theId = item.assignments[i].id;// id find assignments for id?
            var assignment = $.grep(assignments, function(elem, indx){
                return elem['assignment_id'] == theId;
            });
            console.log('assignment:',assignment.length, assignment);

            if(role == 'Learner') {
                //console.log(assignment);// NO module id
                var submitted = $.grep(submissions, function(elem, indx){
                    return elem['assignment_id'] == theId;
                });

                // if submitted.score is null check if locked or available
                if(submitted[0].score == null) { 

                    // if assignment locked use gray FIGURE OUT MODULE LOCKED !
                    //$states = $roots->getModuleStates($req);
                    //compare today date with lock_at (2015-09-01 06:00:00) 
                    if(assignment[0].locked_for_user == 0) {
                    //if(assignment[0].lock_at == null) {
                        content += '<div class="alert alert-success available">';//Available green
                    } else {
                        content += '<div class="alert unavailable">';//Locked grey [figure out locked]
                        locked=true;
                    }
                    //content += '<div class="alert fade in">';
                    //submitted[0].score='0';// instead of null

                }else if(submitted[0].score == 0){
                    content += '<div class="alert alert-info available">';// red alert-danger
                }else{
                    content += '<div  class="alert alert-info available">';//Done blue
                }
                content += '<div class="link" data-url="'+assignment[0].html_url+'">'+assignment[0].name+' </div>';// assignment/ id
                //content += '<div class="link" data-url="'+assignment[0].html_url+'?module_item_id='+assignment[0].module_item_id+'">'+assignment[0].name+' </div>';
                //https://uvu.instructure.com/courses/343331/assignments/1660418?module_item_id=undefined
				//https://uvu.instructure.com/courses/343331/quizzes/464892
				if(locked){ 
                    content += ' Locked, not available yet';
                } else {
                    if(submitted[0].score == null) {
                        content += '- Available to earn '+assignment[0].points_possible+' additional points';
                    } else {
                        content += '- Scored '+submitted[0].score;
                        content += ' out of '+assignment[0].points_possible+' points possible';
                    }
                }
                content += '</div>';// close it
            }

            if(role == 'Instructor') {
                var tags=assignment[0].tags.split(",");
                if(tags.indexOf('C:'+item.name) != -1 ) {
                    content += '<div class="alert alert-success available">';
					var uri = assignment[0].html_url.replace('api/v1/', '');// AHA
                    content += '<div class="link" data-url="'+uri+'">'+assignment[0].name+'</div>';// -api/v1/
                    //content += '<div class="link" data-url="'+uri+'?module_item_id='+assignment[0].module_item_id+'">'+assignment[0].name+' </div>';
                    //https://uvu.instructure.com/ -->api/v1/<-- courses/343331/quizzes/464884?module_item_id=2368118
					
					content += '- Worth '+assignment[0].points_possible+' points.';
                    content += ' ( Tags: '+assignment[0].tags+' )';
                    content += '</div>';
                }
            }
        }

        $('#detailed-body').html(content);
        $('#detailed').modal('show');

        $('.available').on('click',function(e) {
            e.preventDefault();
            e.stopPropagation();
            var url = $(e.currentTarget).find('.link').attr('data-url');
            console.log('url:',url);
            window.open(url, '_blank');	
        });
        //console.log(assignment.length, assignment);
        //console.log(submitted.length, submitted);
    }
//});// end document.ready
