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
	/* Add the content message dynamically, could be different for each role */
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
				if(modlist[i].state == 'completed') {
					modbox +='<div class="modcompleted" data-toggle="tooltip" data-placement="bottom" title="Completed"><i class="icon-check-square-o"></i></div>';
				}
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
                    // Instructor view has no scores available so don't see stars?
                    modbox +='<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';
                }
                
                // + cog edit { image upload? }
                modbox +='</div>';
                
				widt += 191;// add for each moditem 
                $(nuplace).css({ 'width': widt});
				//$(nuplace).parent().css({'overflow': 'hidden' });// id=tab_# class=tab-pane
                //console.log('width:',widt);
                $(nuplace).append(modbox);
            }
        }
    }
    //console.log('modobjs:', modobjs);// to search from
		
    /* click module to see module_items */
    $('.moditem').on('click', function(){
        var modid = $(this).attr('id');
        var mod = $.grep(modobjs, function(elem,index){
            return elem.module_id == modid;
        });
        console.log(modid, mod[0].name, mod);
        var moditems= mod[0].module_items;
        console.log('moditems:',moditems);
        // display module_items in modal detailed-body
        
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
            
            var showPrereq = false;// if locked
            
            for(var pid=0; pid<preids.length; pid++) {
                var itm = $.grep(modobjs, function(elem,index){
                    return elem.module_id == preids[pid];
                });
                //console.log('itm:',itm[0]);// undefined for First or a tab
                
                if(itm.length>0) {
					prename = itm[0].name; 
					console.log('pid:',pid,'prename:',prename);
					//if itm is a tab then ERROR:  Cannot read property 'state' of undefined
					if(itm[0].state == 'locked') { showPrereq=true; console.log('Show locked prereqs'); }
					if(prename != '') {
						prereq +='<li>'+prename+'</li>';
					}
				}
            }
            prereq +='<div class="clearme"></div>';
            prereq +='</div>';// close prereq
            
            if(showPrereq) {
                $('#detailed-body').append(prereq);
            }
		}
		
        //append module_items
        for(var i=0; i<moditems.length; i++) {
			var hasContent=false;
			var displayItem = '<div class="normal">';// addClass if 'description'
            var item='';//'<div class="assignment">';
            var inner = '';// construct separately outer+inner+closer
            if(moditems[i].content.length > 0) { hasContent=true; }
            if(hasContent) {
				// tags: Optional (see iris)
				//var tags = moditems[i].content[0].tags;
				//if(tags.indexOf('Optional') != -1) {
				//	console.log('tags:',tags);
				//	displayItem='<div class="normal last">';
				//}
				
				if(moditems[i].content[0].points_possible > 0) {	
					// before link & icon float:right
					inner+='<div class="points">';
					if(role == 'Learner') {
						inner += 'Score: '+getScore(moditems[i].title)+'/'+moditems[i].content[0].points_possible+'</div>';
					} else {
						inner +='--/'+moditems[i].content[0].points_possible+' pts.</div>';// close points
					}
				}
            }
			if(moditems[i].type!='SubHeader') {
				inner +='<div class="ico">'+chooseIcon(moditems[i].type)+'</div>';
			}
            inner +='<div class="link">';
			//null, locked, unlocked, started, completed
			if(mod[0].state == 'locked') {
				item='<div class="assignment unavailable">';// not a link
			} else {
				item='<div class="assignment available"';// whole div is clickable
				item +='data-url="'+moditems[i].html_url+'?module_item_id='+moditems[i].module_item_id+'">';
			}
			if(moditems[i].type=='SubHeader') { 
				item='<div class="subheader">';// not an assignment
				// if description tag then sort to top of display
				var tags = moditems[i].content[0].tags;
				if(tags.indexOf('Description') != -1) {
					console.log('tags:',tags);// first in list
					displayItem='<div class="first normal">';
				}
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
            }
            //closer 
            var closer = '<div class="clearme"></div>';
				closer +='</div>';//item
				
			//check if Optional tag needs extra div
			// since this is a loop it is added to each
			if(hasContent) {
				// tags: Optional (see iris)
				var tags = moditems[i].content[0].tags;
				if(tags.indexOf('Optional') != -1) {
					console.log('tags:',tags);
					displayItem='<div class="normal last">';
					displayItem+='<div>Optional</div>';
				}
			}
			displayItem += item+inner+closer+'</div>';
            //display.push(displayItem);
			//$('#detailed-body').append(item+inner+closer);
			$('#detailed-body').append(displayItem);
        }
		// sort and place 'description' tag first
		var people = $('#detailed-body'),
			peopleli = people.children('.normal');

		peopleli.sort(function(a,b){
			var an = a.getAttribute('class'),
				bn = b.getAttribute('class');

			if(an > bn) {
				return 1;
			}
			if(an < bn) {
				return -1;
			}
			return 0;
		});
		peopleli.detach().appendTo(people);
		
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
			$(tabVisible).animate({scrollLeft:sat+scrollAmount}, 150, scrollTabVisible);
		}
	}
	$('.aror').hover(function(){
		scrolling=true;
		scrollAmount=50;
		scrollTabVisible();
	}, function(){
		scrolling=false;
	});
	$('.arol').hover(function(){
		scrolling=true;
		scrollAmount=-50;
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
	/* send module_item.type
		return icon to use
	*/
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
                ico='icon-paragraph';//'icon-header';//'icon-file-text-o';
                break;
        }
        return '<i class='+ico+'></i>';
    }
	
	/*
		tabs are built, if student, add stars for score
		
		assignments are done in a new tab
		Reload the page to update progress
		
        for each module
            total = each item points_possible
            if submission for module item
                score = each item submission score
        5 stars = 100% percent
        each star = 20% check nearest 10%
        filled=20, half=10, open=0
		
		Change:
		indicate % of assignments completed out of total # of assignments. 
		I want stars to tell students how many assignments are left in each module
    */
    if(role == 'Learner') {
		// get assignments, submissions, modulescores with RestApi?
		
		// for each modulescores createStars
		console.log('modulescores:',modulescores.length);
		var modivs = $('.moditem');
		for(var i=0; i<modivs.length; i++)
		{
			// find matching array[index] by id
			var module=$.grep(modulescores, function(elem,index) {
				return elem.modid == modivs[i].id;
			});
			console.log('modid:',module[0]);
			var starset=createStars(module[0].score, module[0].total);
			$(modivs[i]).append(starset);
		}
		// Done loading remove loader layer
		$('.loading-indicator-container').hide();
	} else {
		//Instructor
        $('.loading-indicator-container').hide();
        // used storm.css to show the spinner but it changes the Modal. override the css
    }
	
	function createStars(score,total) {
		//console.log('score:',score, 'total:',total);
		var starset = '<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';
        if(total == 0) {
			starset='<div class="stars"></div>';//no assignments = no stars!
		}
		if(score > 0) {
            var percent = (score/total) * 100;
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
	
    /* if Learner get Score for 1 module_item.title
		find assignment matching title
		find submission matching assignment_id
		return score
	*/
    function getScore(title) {
		// find a submission for moditem
		var score='--';// no score
		var asgn1 = $.grep(assignments, function(elem,index){ return elem.name == title; });
		if(asgn1.length>0) {
			var asgnid = asgn1[0].assignment_id;
			var subm1 = $.grep(submissions, function(elem,index) {
				return elem.assignment_id == asgnid;
			});
			//console.log('subm1:',subm1);
			if(subm1.length>0) {
				score = subm1[0].score;
			}
		}
		return score;
    }
    
	/* recursive deep search children */
	function getMyChildren(theObj) {
		var result = null;
		for(var i=0; i<theObj.length; i++) {
			if('children' in theObj[i]) {
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
