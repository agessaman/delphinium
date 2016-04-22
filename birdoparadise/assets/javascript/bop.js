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

$(document).ready(function() {

/* Delphinium functions*/
	/* Add the content message dynamically, could be different for role */
	$('#popinfo').attr('data-content','Click a module to see assignments. Refresh the page to update progress.');
    $('#popinfo').popover();// activate info
    /*
        Bird of Paradise displays modules in tabbed sections 
        configure the Tab/modules structure with Stem
        
        http://getbootstrap.com/components/#nav
        http://daftspunk.github.io/Font-Autumn/
        
        todo:
        reason locked prerequisites
		module box background images - upload and location
        assignment links in locked modules are disabled for student view
    */
	
	//http://diveintohtml5.info/storage.html
	var useStorage = supportsLocalStorage();
	console.log('use localStorage:',useStorage);
	//if(useStorage) { localStorage.setItem('tabVisible',tabVisible); }
	//localStorage["tabVisible"] = tabVisible;// = setItem('tabVisible',tabVisible);
	//localStorage["tabVisible"];// = getItem('tabVisible');
	function supportsLocalStorage()
	{
		try {
			return 'localStorage' in window && window['localStorage'] !== null;
		} catch (e) {
			return false;
		}
	}
/* End Delphinium functions*/

// BOP functions:
    var tabCounter = 0;
	var tabVisible = "#tab_0";//initial tab visible
	if(useStorage) {
		if(localStorage["tabVisible"] != undefined) { tabVisible = localStorage["tabVisible"]; }
	}
    var tabTemplate = "<li role='presentation'>";
		tabTemplate +="<a role='tab' data-toggle='tab' href='#{href}' aria-controls='#{href}'>#{label}</a>";
        tabTemplate +="</li>";
    // display module items when modbox clicked
    var modobjs = [moduledata[0]];// all modules for search include First
    var modlist = [];// modules in current tab only
    var modboxs = [];// all modules in sequence matching tabs
	
    // create tabs and modules from data
    for(var m=0; m<moduledata.length; m++) {
        
        var chld = moduledata[m]['children'];
        for(var c=0; c<chld.length; c++) {
            var tabname=chld[c].name;
            addTab(tabname);// each unit
            // add moditem for each chld[c].module_item
            
            var nuplace = $('#tab_'+c+'body');
            var widt = $(nuplace).width();// increase for each new module
            var mods = chld[c].children;
            modlist = [];// modules in this tab
            //recursive deep search children - do this in the php
			getMyChildren(chld[c]['children']);
            // now display modlist for each tab
            for(i=0; i<modlist.length; i++) {
                
                // dont display if unpublished
                if(modlist[i].published == 0) { continue; }
                //Module box with title, lock, stars & image
            var modbox = '<div id="'+modlist[i].module_id+'" class="moditem" data-locked="'+modlist[i].locked+'">';
                modbox +='<div class="title '+modlist[i].state+'">'+modlist[i].name+'</div>';
                //console.log('state:',modlist[i].state);//null,locked,unlocked,started,completed
                if(modlist[i].state == 'locked') {
                    var prereqids = modlist[i].prerequisite_module_ids;
                    var prename = '';// can have multiple. comma delimited string.
                    var preids = prereqids.split(',');
                    //console.log(modlist[i],': preids:',preids); 
        
                    for(var pid=0; pid<preids.length; pid++) {
                        if(pid>0) { prename+=' & '; }
                        //prename += preids[pid];// maybe just id
						var itm = $.grep(modobjs, function(elem,index){
							return elem.module_id == preids[pid];
						});
						//console.log('itm:',itm[0]);// undefined for First
						
						if(itm.length>0) { prename += itm[0].name; }
						console.log('prerequisite id:',preids[pid], 'name:',prename);
						//if(itm[0].state == 'locked'){ console.log('Show locked prereqs'); }
                    }
				    modbox +='<div class="modlocked" data-toggle="tooltip" data-placement="bottom" title="'+prename+'"><i class="icon-lock"></i></div>';   
                }
				//modbox +='<div class="items">'+modlist[i].module_id+' Items: '+modlist[i].items_count+'</div>';// testing
                
                if(role != 'Learner') {
                    // Instructor view no scores available
                    modbox +='<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';
                }
                
                // + cog edit { image upload? }
                modbox +='</div>';
                
				widt += 191;// add for each moditem 
                $(nuplace).css({ 'width': widt});
				//$(nuplace).parent().css({'overflow': 'hidden' });// id=tab_# class=tab-pane
                //console.log('width:',widt);
                $(nuplace).append(modbox);
				modboxs.push(modlist[i]);
            }
        }
    }
    //console.log('modobjs:', modobjs);// to search from
    
	/*
		tabs are built, now add stars if student
		
		call studentProgress when page loads
		returns student data and builds stars
		
		Reload the page to update progress
		assignments are done in a new tab and would not be updated unless refreshed
    */
		
    /* click module to see module_items */
    $('.moditem').on('click', function(){
        var modid = $(this).attr('id');
        var mod = $.grep(modobjs, function(elem,index){
            return elem.module_id == modid;
        });
        console.log(modid, mod[0].name, mod);
        var moditems= mod[0].module_items;
        console.log('moditems:',moditems);
        // display in modal detailed-body
        
        $('#detailed-body').empty();
        
        // hide if fulfilled
		//append prerequisite_module_ids 
		if(mod[0].prerequisite_module_ids != '') {
            // if prereq id state is not completed show prereq
            
			var prereqids=mod[0].prerequisite_module_ids;
            var prename = '';
            var preids = prereqids.split(',');// multiple
            
            var prereq = '<div class="prereq">';// orange
				prereq +='<div class="ico"><i class="icon-exclamation-triangle"></i> Module Prerequisites<br/></div>';
                prereq +='<div class="prereqnote">Before you can start this module you need to complete the following modules:</div>';
                prereq +='<div class="clearme"></div>';
            
            var showPrereq = false;// unless unlocked
            
            for(var pid=0; pid<preids.length; pid++) {
                var itm = $.grep(modobjs, function(elem,index){
                    return elem.module_id == preids[pid];
                });
                //console.log('itm:',itm[0]);// undefined for First
                
                if(itm.length>0) { prename = itm[0].name; }
                console.log('pid:',pid,'prename:',prename);
                if(itm[0].state == 'locked'){ showPrereq=true; console.log('Show locked prereqs'); }
                if(prename != '') {
                    prereq +='<li>'+prename+'</li>';
                }
            }
            prereq +='<div class="clearme"></div>';
            prereq +='</div>';// close prereq
            
            if(showPrereq) {
                $('#detailed-body').append(prereq);
            }
		}
		
        //append module_items : mod[0].state;//null,locked,unlocked,started,completed
        for(var i=0; i<moditems.length; i++) {
			var hasContent=false;
            var item='';//'<div class="assignment">';
            var inner = '';// construct separately outer+inner+closer
            if(moditems[i].content.length > 0) { hasContent=true; }
            if(hasContent && moditems[i].content[0].points_possible > 0) {
                // before link & icon float:right
                inner+='<div class="points">';
                if(role == 'Learner') {
                // if student 'Score: ##/possible'
                    //JUST .title
                    inner += 'Score: '+getScore(moditems[i].title)+'/'+moditems[i].content[0].points_possible+'</div>';
                } else {
                    inner +='--/'+moditems[i].content[0].points_possible+' pts.</div>';// close points
                }
            }
            inner +='<div class="ico">'+chooseIcon(moditems[i].type)+'</div>';
            inner +='<div class="link">';
			if(mod[0].state == 'locked') {
				item='<div class="assignment unavailable">';
				//inner += ' '+moditems[i].title;// not a link if locked
			} else {
				item='<div class="assignment available"';
				item +='data-url="'+moditems[i].html_url+'?module_item_id='+moditems[i].module_item_id+'">';
				//inner += ' '+moditems[i].title;
				/* whole div is clickable */
			}
			inner +=' '+moditems[i].title+'</div>';
		/*	if(moditems[i].completion_requirement.length > 0) {
                inner += '<div class="required">'+moditems[i].completion_requirement+'</div>';//{"type":"must_submit"}
            }
         */   
            // after link
            if(hasContent && moditems[i].content[0].lock_explanation.length > 0) {
                //console.log('lock_ex len:',moditems[i].content[0].lock_explanation.length);
               inner +='<div class="prereqnote">'+moditems[i].content[0].lock_explanation+'</div>';
            /* WEIRD CONTENT BROKE THIS !!! gets truncated in db !
			length=255 actualCONTENT.length = 366
            lock_explanation:
            "This quiz is part of the module <b>Organizational Controls</b> and hasn&#39;t been unlocked yet.
            <br/>
            <div class='spinner'></div>
            <a style='display: none;' class='module_prerequisites_fallback' href='https://uvu.instructure.com/courses/343331/modules#module"
            */
            }
            //closer 
            var closer = '<div class="clearme"></div>';
				closer +='</div>';//item
            $('#detailed-body').append(item+inner+closer);//(item);
        }
		
		//open available assignments in a new window
		$('.available').on('click',function(e){
			e.preventDefault();
			e.stopPropagation();
			var url = $(e.currentTarget).attr('data-url');
			console.log('url:',url);
			window.open(url, '_blank');	
		});
		
        // trigger modal
        $('#detailed-title').html(mod[0].name);
        $('#itemdetails').modal('show');
    });

	// activate tabs
	var atab = $('a[href='+tabVisible+']');
	$(atab).tab('show');
	//console.log('tabVisible:',tabVisible);
	
    /* 
	   if tab body is < component or window width turn on scroll arrows
       on browser resized, check if arrows are needed
	   on tab change event, check if needed for current tabWidth
		http://getbootstrap.com/javascript/#tabs
	*/
	var docWidth = $( document ).width();
	var parentWidth = $('.page').parent().width();
	$('#tabs').on('shown.bs.tab', function(e){
		tabVisible = e.target.hash;// changed
		if(useStorage) { localStorage.setItem('tabVisible',tabVisible); }
		arrowsNeeded();
	});
    $( window ).on('resize', function(e) {
		docWidth = $( document ).width();// changed
		arrowsNeeded();
	});
    arrowsNeeded();// initial display
    function arrowsNeeded() {
		var needArrows=false;
		var tabWidth = $(tabVisible+'body').width();
        if(tabWidth > docWidth) { needArrows=true; }
		if(tabWidth > parentWidth) { needArrows=true; }
        if(needArrows) {
            $('.arol, .aror').show();
        } else { $('.arol, .aror').hide(); }
		//console.log(tabVisible,tabWidth,docWidth,needArrows);
    }
	
	/*
		Scroll the module items by hover on arrows
		http://stackoverflow.com/questions/2039750/jquery-continuous-animation-on-mouseover
		$( selector ).hover( handlerIn, handlerOut )
	*/
	var scrolling=false;
	var scrollAmount=0;
	function scrollTabVisible() {
		if(scrolling){
			var sat = $(tabVisible).scrollLeft();
			$(tabVisible).animate({scrollLeft:sat+scrollAmount}, 300, scrollTabVisible);
		}
	}
	$('.aror').hover(function(){
		scrolling=true;
		scrollAmount=100;
		scrollTabVisible();
	}, function(){
		scrolling=false;
	});
	$('.arol').hover(function(){
		scrolling=true;
		scrollAmount=-100;
		scrollTabVisible();
	}, function(){
		scrolling=false;
	});
	
	/*
		construct a tab from template object
	*/
    function addTab(tabname) {
        var label = tabname;
            id = "tab_" + tabCounter,
            li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ) ),
            tabContentHtml = "<div id='" + id + "' role='tabpanel' class='tab-pane fade'>";
			tabContentHtml +="<div id='"+id+"body' class='tabody'></div></div>";
        
        $('#tablist').append(li);
        $('#tabdy').append(tabContentHtml);
		if(tabCounter==0){ $("tab_" + tabCounter).addClass('in'); }
        tabCounter++; 
    }

    function chooseIcon(type) {
        //console.log('type:',type);
        var ico = 'icon-book';

        switch(type){
            case 'Assignment':
                ico='icon-pencil-square';
                break;
            case 'Discussion':
                ico='icon-comments';
                break;
            case 'ExternalUrl':
                ico='icon-link';
                break;
            case 'ExternalTool':
                ico='icon-wrench';
                break;
            case 'File':
                ico='icon-cloud-download';
                break;
            case 'Quiz':
                ico='icon-question-circle';
                break;
            case 'SubHeader':
                ico='icon-file-text-o';
                break;
        }
        return '<i class='+ico+'></i>';
    }
    /*
        for each module
            total = each item points_possible
            if submission for module item
                earned = each item subm score
        5 stars = percent completed
        each star = 20% check nearest 10%
        filled=20, half=10, open=0

        return stars div
        
        move this function to RestApi.php
    */
    if(role == 'Learner') {
        OLDstudentProgress();
		//studentProgress();// much slower
	} else {
        // Done loading remove loader layer
        $('.loading-indicator-container').hide();
        // used storm.css to get the spinner to show but it changes the Modal. override the css
    }
	function studentProgress() {
        var count = 0;
		var modivs = $('.moditem');// array of modules displayed in tabs
		for(var i=0; i<modivs.length; i++)
		{
			// convert getStars calculation to php function that returns score, total
			//var starset = getStars(modboxs[i].module_id);//send module id
			
			//send module.id : returns score,total
			var modid=modivs[i].id;
			var promise = $.get('calculateStars', {'modid': modid});
			promise.then(function (data) {
                
				count += 1;
                
				console.log(count, data.score, data.total, data);
				var starset=createStars(data.score, data.total);
				$(modivs[i]).append(starset);
				
                // Done loading so remove loader layer
				//console.log('promise:', count, data);
				if(count == modivs.length) {
				    $('.loading-indicator-container').hide();
                }
                
			}).fail(function (data2) { console.log('failCalcStars:',data2); });
		}
	}
	function createStars(score,total) {
		
		var starset = '<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';
        if(score > 0) {
            //console.log('score:',score, 'total:',total);
            var percent = (score/total) *100;
            console.log('percent=',percent);
            starset='<div class="stars">';
            for(var s=1; s<6; s++) {
                if(percent > s*20) {
                    starset += '<i class="icon-star"></i>';
                } else if(percent < (s*20) && percent >= (s*20)-10) {
                    starset += '<i class="icon-star-half-o"></i>';
                } else {
                    starset += '<i class="icon-star-o"></i>';
                }
            }
            starset += '</div>';
        }
        return starset;
	}
	
	function OLDstudentProgress() {
		var modivs = $('.moditem');// array of modules displayed in tabs
		//console.log(modivs.length, modboxs.length);
		//https://laravel.com/docs/5.2/controllers#basic-controllers
		// call functions in php using RestApi.php
		var promise = $.get('getAssignments');//faster
        promise.then(function (data) {
            console.log('assignments:',data);
            assignments= data;
            
			var promises = $.get('getSubmissions');
			promises.then(function (data) {
				//console.log('submissions:',data);
				subms = $.grep(data, function(elem,index){ return elem.score > 0; });
				console.log('subms:',subms);
				// calc filled stars from submissions and assignments
                
				for(var i=0; i<modivs.length; i++)
				{
					// convert getStars calculation to php function that returns score, total
					var starset = getStars(modboxs[i].module_id);//send module id
					
					//php: $.get('calculateStars');// returns score,total
					// createStars(score,total);
					
					$(modivs[i]).append(starset);
				}
				
                // Done loading remove loader layer
                $('.loading-indicator-container').hide();// could not get this to show
                
			}).fail(function (data2) { console.log('failSub:',data2); });
        }).fail(function (data2) { console.log('failAsgn:',data2); });
    }

    function getStars(modid){
        // construct from modid
        var mod1 = $.grep(modobjs, function(elem,index){ return elem.module_id == modid; });
        //console.log('mod1:',modid, mod1);
        var total=0, score=0;
        var moditems = mod1[0].module_items;
        for(var i=0; i<moditems.length; i++) {

            // find a submission for moditem
            var title=moditems[i].title;
            var asgn1 = $.grep(assignments, function(elem,index){ return elem.name == title; });

            if(asgn1.length>0) {
                if(moditems[i].content.length>0){
                    total += moditems[i].content[0].points_possible;
                    var asgnid = asgn1[0].assignment_id;
                    //console.log(modid,'asgn1.assignment_id:',asgnid);
                    var subm1 = $.grep(subms, function(elem,index) {
                        return elem.assignment_id == asgnid;
                    });
                    //console.log('subm1:',subm1);
                    if(subm1.length>0) {
                        score += subm1[0].score;
                    }
                }
            }
        }
        // default: no score yet empty stars
        var starset = '<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';

        if(score > 0) {
            console.log(modid, 'score:',score, 'total:',total);
            var percent = (score/total) *100;
            console.log('percent=',percent);
            starset='<div class="stars">';
            for(var s=1; s<6; s++) {
                if(percent > s*20) {
                    starset += '<i class="icon-star"></i>';
                } else if(percent < (s*20) && percent >= (s*20)-10) {
                    starset += '<i class="icon-star-half-o"></i>';
                } else {
                    starset += '<i class="icon-star-o"></i>';
                }
            }
            starset += '</div>';
        }
        return starset;
    }
    
    // getScore for 1 module_item.title
    function getScore(title) {
        // find a submission for moditem
        var score='--';// no score
        //var title=moditem.title;
        var asgn1 = $.grep(assignments, function(elem,index){ return elem.name == title; });
        if(asgn1.length>0) {
            //if(moditem.content.length>0){
                var asgnid = asgn1[0].assignment_id;
                //console.log(modid,'asgn1.assignment_id:',asgnid);
                var subm1 = $.grep(subms, function(elem,index) {
                    return elem.assignment_id == asgnid;
                });
                //console.log('subm1:',subm1);
                if(subm1.length>0) {
                    score = subm1[0].score;
                }
            //}
        }
        return score;
    }
    
	//console.log('moduledata[0]:', moduledata[0]);
    //var cmods = [];// test deep search for ['children']
	//var chld = moduledata[0]['children'];// 4 tabs
	//getMyChildren(chld[1]['children']);// tab 2 children
	function getMyChildren(theObj) {
		var result = null;
		for(var i=0; i<theObj.length; i++) {
			if('children' in theObj[i]) {
				//cmods.push(theObj[i]);// test: comment out modobjs & modlist
				modobjs.push(theObj[i]);// all objects
				modlist.push(theObj[i]);// build moduledata
				result = getMyChildren(theObj[i].children);
				if(result) { break; }
			}
        }
		return result;
	}
    //console.log('cmods:', cmods.length, cmods);
//End document.ready
});
