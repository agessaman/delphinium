$(document).ready(function(){
	//var spinner = new Spinner(opts).spin(target);//loading

//var is already available from the php $this->page
    ////var assignments={{assignments|raw}};
    //console.log('assignments.len:'+assignments.length);//60
    //console.log(assignments[1]);
    //get points_possible Does NOT have Tags:
    //$('#submissions').append('assignments.len:'+assignments.length+'<br/>');
    
    ////var submissions = {{submissions|raw}};//json
    //console.log('submissions.len:'+submissions.length);
    //console.log(submissions);//all
    //$('#submissions').append('submissions.len:'+submissions.length+'<br/>');

    var tagged =$.grep(submissions, function(elem, indx){
        return elem['tags'] != "";
    });
    //console.log('tagged.len'+tagged.length);
    //console.log(tagged);
	//$('#submissions').append('tagged.len'+tagged.length+'<br/>');
    
    var tagList=[];
    for(i=0; i<tagged.length; i++) {
        //remove tags that do not start with 'C:'
        var tagarray = tagged[i]['tags'].split(', ');
        for(var t=0; t<tagarray.length; t++) {
            if(tagarray[t].substring(0,2) != 'C:') {
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
        //$('#submissions').append(tdetails);
    }
    console.log(tagList.length, 'tagList:'+tagList);
    //$('#submissions').append('tagList: '+tagList);
    

/*
    loop thru tagList to sort tagged submissions into groups, 
    for each submission in each group count points possible & amount

get points_possible from assignments that match each group assignment_id
assignments do NOT have Tags: submissions do NOT have points_possible

construct the data needed for Competency
for each tagged get assignment points_possible
*/
    var data =[];// json for d3
    var gTotal=0, gAmount=0;
    for(var l=0; l<tagList.length; l++) {
		// group by tag
        var group = $.grep(tagged, function(elem, indx){
            if(hasTag(elem, tagList[l])) { return elem; }
        });
        //console.log(group.length, group);
		// for each group
        for(var g=0; g<group.length; g+=1) {
            gAmount += group[g].score;
			//add up scores and points possible
            for(var a=0; a<assignments.length; a++) {
                if(assignments[a].assignment_id == group[g].assignment_id) {
                    gTotal += assignments[a].points_possible;
                }
            }    
        }
        
        // construct data for D3
        var name = tagList[l].substring(2,tagList[l].length);
        //xcale((data[i].percent/100)*maxTotal)
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

    var big=[];//find largest total
    for(i=0; i<data.length; i++) {
        big.push(data[i].total);
    }
    //console.log(Math.max.apply(null,big));
    var maxTotal=Math.max.apply(null,big);// for d3.scale
    //console.log('maxTotal:'+maxTotal);
    var grid=[10,20,30,40,50,60,70,80,90];//vertical % tick marks

	// NOW D3 it!
    ////var competenciesSize='{{competenciesSize}}';
    
	var competenciesView = d3.select("#competenciesView");
	var competenciesSVG = d3.select("#competenciesSVG");
	var competenciesHeight = data.length*50;
	var competenciesWidth = 250;
	if(competenciesSize == "Small"){
		competenciesSVG.attr('width', competenciesWidth / 2)
				.attr('height', competenciesHeight / 2);
		competenciesView.attr('transform', "scale(.5)");
	}else if(competenciesSize == "Medium"){
		competenciesSVG.attr('width', competenciesWidth)
				.attr('height', competenciesHeight);
	}else{
		competenciesSVG.attr('width', competenciesWidth * 1.5)
				.attr('height', competenciesHeight * 1.5);
		competenciesView.attr('transform', "scale(1.5)");
	}
    
	var lineColor='#b0bf6c';
    var amountColor='#588238';
    ////var competenciesCount = {{competencies|raw}};// show all UNUSED?
	////var competenciesAnimate={{competenciesAnimate}};
    var competencies = d3.select("#competenciesView");
    var xcale = d3.scale.linear()
        .domain([0, maxTotal+2])
        .range([0,competenciesWidth]);
    //console.log(xcale(80), xcale(398), xcale(598));
    
    // all data or competenciesCount
	for (var i = 0; i < data.length; i++) {
		competencies.append('text')
			.text(data[i].name+' '+data[i].percent+'%')
            .attr('font-size', '1em')
			.attr('y', i * 50 + 10);
        // grid lines for each - behind the transparent fill
		if(competenciesAnimate == true){
			competencies.append('rect')
					.attr('height', 25)
					.attr('width', 0)
					.attr('fill', 'white')
					.attr('y', i * 50 + 18)
					.transition()
						.delay(500*i)
						.duration(1000)
						.attr('width', xcale((data[i].percent/100)*maxTotal))
						.attr('fill', amountColor)
						.ease('bounce');//easein
		} else {
			competencies.append('rect')
					.attr('height', 25)
					.attr('y', i * 50 + 18)
					.attr('width', xcale((data[i].percent/100)*maxTotal))
					.attr('fill', amountColor);
		}
		//grid lines
		competencies.selectAll("gridline")
			.data(grid)
			.enter()
			.append("line")
			.attr("x1",function(d, i){ return xcale((grid[i]/100)*maxTotal); })
			.attr("y1", i * 50 + 18)
			.attr("x2",function(d, i){ return xcale((grid[i]/100)*maxTotal); })
			.attr("y2",i * 50 + 43)
			.style("stroke",'silver')
			.style("stroke-width",1);
	
		// outline			
		competencies.append('rect')
				.attr('height', 25)
				.attr('width', xcale(maxTotal))
				.attr('stroke-width', '2')
				.attr('stroke', 'gray')
				.attr('fill', 'none')
				.attr('y', i * 50 + 18);
        //percent completed
		competencies.append('text')
                .text(data[i].percent+'%')
                .attr('fill', '#f0f0f0')
                .attr('font-size', '0.9em')
                .attr('x', 10)
                .attr('y', i*50+35);
		/*
		// points/total
        competencies.append('text')
                .text(data[i].amount+'/'+data[i].total)
                .attr('fill', '#f0f0f0')
                .attr('font-size', '0.9em')
                .attr('x', 10)
                .attr('y', i*50+35);
		*/
	};
});

