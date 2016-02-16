var div;


$(document).ready(function(){
	/*
        Competencies use
        submissions for Tags & scores
        assignments for points_possible
    */
    div = d3.select("body").append("div")
        .attr("class", "tooltip")
        .style("opacity", 0);
    
	////var rawData = {{ rawData|raw}};
	//has locked
	//module_items[i].content[0].title & html_url or url
	//module_items[i].content[0] has points_possible & tags
	// replaces assignments
	
    
	////var assignments={{assignments|raw}};
    //console.log(assignments.length, assignments);
    //get points_possible
    
    ////var submissions = {{submissions|raw}};//json
    //console.log(submissions.length, submissions);//all
    //get tags & score
    // related by assignment_id
    
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
        tagged[i]['tags']=tagarray.join();
        var tdetails = 'subm['+i+']';
            tdetails+= ' tags:'+tagged[i].tags+' [score:'+tagged[i].score+']<br/>';
    }
    console.log(tagList.length, 'tagList:'+tagList);

/*
    loop thru tagList to sort tagged submissions into groups, 
    for each submission in each group count points possible & amount

get points_possible from assignments that match each group assignment_id
assignments do NOT have Tags: submissions do NOT have points_possible

construct the data needed for Competency
for each tagged get assignment points_possible
*/
    var details=[];// for modal '#detailed' body content
    var data =[];// json for d3
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
                    gTotal += assignments[a].points_possible;
                    // add matching assignments ids for modal view
                    details[l]['assignments'].push({'id':assignments[a].assignment_id});
                }
            }    
        }
        // construct data for D3
        // xcale((data[i].percent/100)*maxTotal)
		var percent = Math.round(gAmount/gTotal*100);
        data.push({"name":name,"total":gTotal,"amount":gAmount,"percent":percent});
    }
    
    function hasTag(obj,tag) {
        var tagarray = obj['tags'].split(',');
        var marked=false;
        //console.log(tagarray);//["C:Ideas", "C:Align", "C:People"]
        for(var t=0; t<tagarray.length; t++) {
            if(tagarray[t].toLowerCase() == tag.toLowerCase()) { marked=true; }
        }
        return marked;
    }
    console.log(data.length,data);
    console.log(details.length,details);
    
    var big=[];
    for(i=0; i<data.length; i++) {
        big.push(data[i].total);
    }
    //find largest total for d3.scale
    var maxTotal=Math.max.apply(null,big);
    big=null;// done with big
    //console.log('maxTotal:'+maxTotal);
    var grid=[20,30,40,50,60,70,80,90];//vertical % tick marks

	// Only show the d3 if data is valid
	
	// NOW D3 it!
    //php echo '<div id="loader" class="container spinner"></div>';
    //$('#loader').remove();//.removeClass('spinner');
    // not visible while everything loads
    // try progress bar? https://octobercms.com/docs/ui/progressbar
    
    // should construct this dynamically
	var competenciesView = d3.select("#competenciesView");
	var competenciesSVG = d3.select("#competenciesSVG");
    var rowHeight = 45;// a property?
	var competenciesHeight = data.length*rowHeight;
	var competenciesWidth = 250;// could be a property?
    ////var competenciesSize='{{competenciesSize}}';
	if(competenciesSize == "small"){
		competenciesSVG.attr('width', competenciesWidth / 1.5)
				.attr('height', competenciesHeight / 1.5);
		competenciesView.attr('transform', "scale(.66)");
	}else if(competenciesSize == "large"){
		competenciesSVG.attr('width', competenciesWidth * 1.5)
				.attr('height', competenciesHeight * 1.5);
        competenciesView.attr('transform', "scale(1.5)");
	}else{
		competenciesSVG.attr('width', competenciesWidth)
				.attr('height', competenciesHeight);// default
	}
    
	////var competenciesColor=''{{competenciesColor}}''; 
    //set with a color picker in backend
	////var competenciesAnimate={{competenciesAnimate}};
    
    // add svg and group to default.htm dynamically
    var competencies = d3.select("#competenciesView");// a <g>roup
    var xcale = d3.scale.linear()
        .domain([0, maxTotal+2])
        .range([0, competenciesWidth]);
    //console.log(xcale(80), xcale(398), xcale(598));
    
    // show all data or competenciesCount
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
	
		// outline			
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
                .attr('fill', '#f0f0f0')
                .attr('font-size', '0.9em')
                .attr('x', 10)
                .attr('y', i*rowHeight+31);
        
        //invisble btn of each bar for tooltip
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
            .attr('data-value', function(d) { return data[i].amount+' points.<br/>'+data[i].total+' possible'; })
            .on('mouseover', function (d) {
                console.log(d3.event.currentTarget.id);//
                var text = $(d3.event.currentTarget).attr('data-value');
                addTooltip(text);
            })
            .on('mousemove', function(d) {
                var tx = d3.event.clientX;
                var ty = d3.event.clientY-38;//upper right of mouse
                ///console.log('tx:'+tx);
                $('.tooltip').css({'left':tx + 'px','top':ty + 'px','background':competenciesColor});
                //$('.tooltip').css({'background':competenciesColor+' !important'});
            })
            .on('mouseout', function (d) {
                removeTooltip();
            })
            .on('mouseup', function(d) {
                
                div.style("opacity",0);// removeTooltip immediately
                console.log('clicked: '+$(d3.event.currentTarget).attr('data-name'));
                var detailItem = $.grep(details, function(elem, indx){
                    return elem['name'] == $(d3.event.currentTarget).attr('data-name');
                });
                console.log(detailItem[0]);
                displayDetails(detailItem[0]);
            });
	}
    // end for
});


/*
display assignments that match submissions tag groups
display rows for details {'name': align , id:[{#, #, #, #}]}

grey = locked
blue = done
green=available to do still

*/
function displayDetails(item) {
    
    $('.modal-title').html(item.name+' Competency Details');
    var content='';
    var locked=false;
    var completed=false;
    for(var i=0; i<item.assignments.length; i++) {
        //construct modal content
        var theId = item.assignments[i].id;// id find assignments for id?
        var assignment = $.grep(assignments, function(elem, indx){
            return elem['assignment_id'] == theId;
        });
	//console.log('Find module_id');//
    //console.log(assignment);// NO module id
        var submitted = $.grep(submissions, function(elem, indx){
            return elem['assignment_id'] == theId;
        });
        
        // if submitted.score is null check if locked or available
        if(submitted[0].score == null) { 
            // if assignment locked use gray FIGURE OUT MODULE LOCKED !
            if(assignment[0].lock_at == null) {
                content += '<div class="alert alert-success">';//Available green
            } else {
                content += '<div class="alert">';//Locked grey [figure out]
                locked=true;
            }
            //content += '<div class="alert fade in">';
            ///submitted[0].score='0';// instead of null
        }else if(submitted[0].score == 0){
            content += '<div class="alert alert-info">';// red alert-danger
        }else{
            content += '<div  class="alert alert-info">';//Done blue
        }
        content += '<a target="_blank" href="'+assignment[0].html_url+'">'+assignment[0].name+'</a>';
        if(locked){ 
            content += ' Locked not available yet';
        } else {
            if(submitted[0].score == null) {
                content += ' Available to earn '+assignment[0].points_possible+' additional points';
            } else {
                content += ' Scored '+submitted[0].score;
                content += ' out of '+assignment[0].points_possible+' points possible';
            }
        }
        
        content += '</div>';
    }
    //console.log(assignment.length, assignment);
    //console.log(submitted.length, submitted);
    $('.modal-body').html(content);
    $('#detailed').modal();
}

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
