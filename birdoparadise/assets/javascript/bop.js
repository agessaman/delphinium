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

/* Delphinium functions */
	/* Add the content message dynamically, could be different for each role */
	$('#bop_popinfo').attr('data-content','Click a module to see assignments. Refresh the page to update progress.');
    $('#bop_popinfo').popover();// activate info
	$('#bop_cog').on('click', function(e){
		$('#bop_configuration').modal('show');
	});
    /*
        Bird of Paradise displays modules in tabbed sections 
        configure the Tab/modules structure with Stem
        
        http://getbootstrap.com/components/#nav
        http://daftspunk.github.io/Font-Autumn/
        
        todo:
        reason locked prerequisites
		module box background images - upload and location
    */
	
	/* http://diveintohtml5.info/storage.html
		if(useStorage) { localStorage.setItem('tabVisible',tabVisible); }
		localStorage["tabVisible"] = tabVisible;// = setItem('tabVisible',tabVisible);
		localStorage["tabVisible"];// = getItem('tabVisible');
	*/
	var useStorage = supportsLocalStorage();
	console.log('use localStorage:',useStorage);
	function supportsLocalStorage()
	{
		try {
			return 'localStorage' in window && window['localStorage'] !== null;
		} catch (e) {
			return false;
		}
	}
/* End Delphinium functions */

/* BOP functions: */
    var tabCounter = 0;
	var tabVisible = "#tab_0";//initial tab visible
	if(useStorage) {
		if(localStorage["tabVisible"] != undefined) { tabVisible = localStorage["tabVisible"]; }
		// could store modobjs & modlist[tab][box,box,..]
	}
    var tabTemplate = "<li role='presentation'>";
		tabTemplate +="<a role='tab' data-toggle='tab' href='#{href}' aria-controls='#{href}'>#{label}</a>";
        tabTemplate +="</li>";
    
    var modobjs = [moduledata[0]];// all modules for search includes First
    var modlist = [];// modules in current tab only
	
    /* create tabs and module boxes from data */
    for(var m=0; m<moduledata.length; m++) {
        
        var chld = moduledata[m]['children'];
        for(var c=0; c<chld.length; c++) {
            var tabname=chld[c].name;
            addTab(tabname);
            /* add moditem for each chld[c].module_item */
            var nubody = $('#tab_'+c+'body');
            var widt = $(nubody).width();// increase for each new module
            var mods = chld[c].children;
            modlist = [];// modules in this tab only
            /* recursive deep search for children */
			getMyChildren(chld[c]['children']);// push them to modobjs & modlist
            
			/* display modlist as box for each tab */
            for(i=0; i<modlist.length; i++) {
                /* construct module box with title, stars & image? */
            var modbox = '<div id="'+modlist[i].module_id+'" class="moditem" data-prereq="'+modlist[i].prerequisite_module_ids+'">';
                modbox +='<div class="title">'+modlist[i].name+'</div>';
            
                if(role != 'Learner') {
                    /* Instructor view has no scores available so use empty stars */
                    modbox +='<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';
                }
                modbox +='</div>';
                
				widt += 191;// add to width for each module box added 
                $(nubody).css({ 'width': widt});
                //console.log('tab body width:',widt);
                $(nubody).append(modbox);
            }
        }
    }
    //console.log('modobjs:', modobjs);// all modules for search includes First
		
    /* click module box to display module_items in modal */
    $('.moditem').on('click', function() {
        var modid = $(this).attr('id');// data-prereq & data-state if Learner
        var mod = $.grep(modobjs, function(elem,index){
            return elem.module_id == modid;
        });
        console.log(modid, mod[0].name, mod);
        var moditems= mod[0].module_items;
        console.log('moditems:',moditems);
        // display module_items in modal detailed-body
        
		$('#bop_detailed-body').empty();
        
         /* if prerequisite state is not completed show. don't if fulfilled */
		if(mod[0].prerequisite_module_ids != '') {
			var prereqids=mod[0].prerequisite_module_ids;
            var prename = '';
            var preids = prereqids.split(',');// multiple
            
            var prereq = '<div class="prereq">';// orange
				prereq +='<div class="ico"><i class="icon-exclamation-triangle"></i> Module Prerequisites<br/></div>';
                prereq +='<div class="prereqnote">Before you can start this module you need to complete the following modules:</div>';
                prereq +='<div class="clearme"></div>';
            
            var showPrereq = false;// if locked
            
            for(var pid=0; pid<preids.length; pid++) {
				/* find the prerequisite module.name */
				var itm = $.grep(modobjs, function(elem,index){
                    return elem.module_id == preids[pid];
                });
				
                //console.log('itm:',itm[0]);// undefined for First & modules used for tabs
				/* if it is a tab then ERROR:  Cannot read property 'state' of undefined
					Always use empty modules for tabs
				*/
                if(itm.length>0) {
					prename = itm[0].name; 
					console.log('pid:',pid,'prename:',prename);
					
					/* find modulescores.state only available if Learner */
					if(role == 'Learner') {
						var premod = $.grep(modulescores, function(elem,index){
							return elem.modid == preids[pid];
						});
						
						if(premod[0].state == 'locked') {
							showPrereq=true;
							console.log('Show locked prereqs');
						}
					/* IF instructor show it always */
					} else { showPrereq = true; }
					
					if(prename != '') {
						prereq +='<li>'+prename+'</li>';
					} else {
						prereq +='<li>'+preids[pid]+'</li>';// some do not ?
					}
				}
            }
            prereq +='<div class="clearme"></div>';
            prereq +='</div>';// close prereq
            
            if(showPrereq) {
                $('#bop_detailed-body').append(prereq);
            }
		}
		
        /* display module_items */
        for(var i=0; i<moditems.length; i++) {
			var hasContent=false;
			var displayItem = '<div class="normal">';// addClass if 'description'
            var item='';// class="assignment" or "subheader"
            var inner = '';// construct separately outer+inner+closer
            if(moditems[i].content.length > 0) { hasContent=true; }
            if(hasContent) {
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
			/* null, locked, unlocked, started, completed */
			if($(this).attr('data-state') == 'locked') {
				item='<div class="assignment unavailable">';
			} else {
				item='<div class="assignment available"';// whole div is clickable
				//item +='data-url="'+moditems[i].html_url+'">';// displays JSON
                item +='data-url="'+moditems[i].html_url+'?module_item_id='+moditems[i].module_item_id+'">';
			}
			if(moditems[i].type=='SubHeader') { 
				item='<div class="subheader">';// not an assignment
				/* if description tag then sort to top of list
					else its used to separate module items
				*/
				var tags = moditems[i].content[0].tags;
				if(tags.indexOf('Description') != -1) {
					console.log('tags:',tags);// first in list
					displayItem='<div class="first normal">';
				}
			}
			inner +=' '+moditems[i].title+'</div>';
		/*	if(moditems[i].completion_requirement.length > 0) {
				inner += '<div class="required">'+moditems[i].completion_requirement+'</div>';// {"type":"must_submit"}
			}
		*/
            // after link
            if(hasContent && moditems[i].content[0].lock_explanation.length > 0) {
                //console.log('lock_ex len:',moditems[i].content[0].lock_explanation.length);// greater than 255 
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
			$('#bop_detailed-body').append(displayItem);
        }
		// sort and place 'description' tag first
		var people = $('#bop_detailed-body'),
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
		
        // open the item details modal
        $('#bop_detailed-title').html(mod[0].name);
        $('#bop_details').modal('show');
    });

	// activate tabs
	var atab = $('a[href='+tabVisible+']');
	$(atab).tab('show');
	//console.log('tabVisible:',tabVisible);
	
    /* if tab body is < component or window width turn on scroll arrows
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
	
	/* Scroll the module items by hover on arrows
		http://stackoverflow.com/questions/2039750/jquery-continuous-animation-on-mouseover
		$( selector ).hover( handlerIn, handlerOut )
	*/
	var scrolling=false;
	var scrollAmount=0;
	function scrollTabVisible() {
		if(scrolling){
			var sat = $(tabVisible).scrollLeft();
			$(tabVisible).animate({scrollLeft:sat+scrollAmount}, 100, scrollTabVisible);
		}
	}
	$('.aror').hover(function(){
		scrolling=true;
		scrollAmount=60;
		scrollTabVisible();
	}, function(){
		scrolling=false;
	});
	$('.arol').hover(function(){
		scrolling=true;
		scrollAmount=-60;
		scrollTabVisible();
	}, function(){
		scrolling=false;
	});
	
	/* construct a tab from template object
		replace the label & id
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
        var ico = 'icon-book';// default

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
        }
        return '<i class='+ico+'></i>';
    }
	
	/*
		tabs are built, if student, add stars for score
		
		assignments are done in a new tab
		Reload the page to update progress
		
        for each module
            total = sum of each item points_possible
            if submission for module item
                score = sum of each item submission score
        5 stars = 100% percent
        each star = 20% check nearest 10%
        filled=20, half=10, open=0
	*/
    // assignments, submissions, modulescores are only available if Learner
    if(role == 'Learner') {
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
			//$(modivs[i]).title addClass module[0].state
			$(modivs[i]).attr('data-state',module[0].state).find('.title').addClass(module[0].state);
			//if completed or locked $(modivs[i]).append();
			if(module[0].state == 'completed') {
				$(modivs[i]).append('<div class="modcompleted" data-toggle="tooltip" data-placement="bottom" title="Completed"><i class="icon-check-square-o"></i></div>');
			}
			
			if(module[0].state == 'locked') {
				var prereqids = $(modivs[i]).attr('data-prereq');// module.prerequisite_module_ids;
				var prename = '';// can have multiple in comma delimited string
				var preids = prereqids.split(',');
				//console.log('preids:',preids);
	
				for(var pid=0; pid<preids.length; pid++) {
					if(pid>0) { prename+=' & '; }
					//prename += preids[pid];// maybe just id
					var itm = $.grep(modobjs, function(elem,index){
						return elem.module_id == preids[pid];
					});
					//console.log('itm:',itm[0]);// undefined for First & Tabs
					
					if(itm.length>0) { prename += itm[0].name; }
					console.log('prerequisite id:',preids[pid], 'name:',prename);
				}
				if(preids[pid] == undefined) { console.log('prerequisite id is undefined'); }
				$(modivs[i]).append('<div class="modlocked" data-toggle="tooltip" data-placement="bottom" title="To unlock, complete '+prename+'"><i class="icon-lock"></i></div>');
			}
		}
		// Done loading remove loader layer
		$('.loading-indicator-container').hide();
	} else {
		// Instructor
        $('.loading-indicator-container').hide();
        // used storm.css to show the spinning loader, override modal-header the css
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
	
    /* if Learner, get Score for 1 module_item.title
		find assignment matching title
		find submission matching assignment_id
		return score
	*/
    function getScore(title) {
		// find a submission for moditem
		var score='--';// no score
		var asgn1 = $.grep(bop_assignments, function(elem,index){ return elem.name == title; });
		if(asgn1.length>0) {
			var asgnid = asgn1[0].assignment_id;
			var subm1 = $.grep(bop_submissions, function(elem,index) {
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
				// dont use if unpublished
				if(theObj[i].published == 1) {
					modobjs.push(theObj[i]);// all objects
					modlist.push(theObj[i]);// build moduledata, modules in tab
					result = getMyChildren(theObj[i].children);
					if(result) { break; }
				}
			}
        }
		return result;
	}

//End document.ready
});
