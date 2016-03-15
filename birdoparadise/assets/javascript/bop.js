$(document).ready(function() {
    
    /*
        Bird of Paradise:
        
        http://getbootstrap.com/javascript/
        http://getbootstrap.com/components/#nav
        http://daftspunk.github.io/Font-Autumn/    icon-star, star-half-o, star-o, icon-lock
        
        todo: Deep search, Locked,
        Tooltip popover for reason locked prerequisites
        Stars or progress bar?
		module bkg images
        assignment links in locked modules are disabled for student view
    */
	var stateColors = {locked: "#8F8F8F", unlocked: "#588238", started: "#5eacd4", completed: "#143D55"};
    var backColors = {locked: "#DDDDDD", unlocked: "#588238", started: "#5eacd4", completed: "#133D55"};
    // colors from iris
    
    var tabCounter = 0;
    var tabTemplate = "<li role='presentation'>";
		tabTemplate +="<a role='tab' data-toggle='tab' href='#{href}' aria-controls='#{href}'>#{label}</a>";
        tabTemplate +="</li>";
    // store individual module objects for display when moditem clicked
    var modobjs = [];
    
    // create units and modules from data
    for(var m=0; m<moduledata.length; m++) {
        
        var chld = moduledata[m]['children'];
        for(var c=0; c<chld.length; c++) {
            var tabname=chld[c].name;
            addTab(tabname);// each unit
            // add moditem for each chld[c].module_item
            
            var nuplace = $('#tab_'+c+'body');
            var widt = $(nuplace).width();// increase for each new module
            var mods = chld[c].children;
            var modlist = [];// modules in this tab
    //do this in Modulemap after getModules in another function ?
            for(var i=0; i<mods.length; i++) {
                // individual module objects for searching items
                modobjs.push(mods[i]);// all
                modlist.push(mods[i]);// modules in this tab
                // if module has children add it
                if(mods[i].children.length > 0 ) {
                    var len = mods[i].children.length;
                    console.log(mods[i].name+' children: '+len);
                     
                    //DEEP CHILDREN: $.each(  http://api.jquery.com/category/utilities/
                    
                    //add to other array
                    for(var mc=0; mc<len; mc++) {
                        // & child of child of child ?
                        modobjs.push(mods[i].children[mc]);
                        modlist.push(mods[i].children[mc]);
                    }
                }
            }
            // now display modlist array, gets reset for each tab
            for(i=0; i<modlist.length; i++) {
                //Module box with title, lock, stars & image
            var modbox = '<div id="'+modlist[i].module_id+'" class="moditem" data-locked="'+modlist[i].locked+'">';
                modbox +='<div class="title">'+modlist[i].name+'</div>';
                //var lock = modlist[i].state;
                //console.log('locked:',modlist[i].state);//null,locked,unlocked,completed
                if(modlist[i].state == 'locked') {
                    var prereqids = modlist[i].prerequisite_module_ids;
                    var prename = '';// can have multiple. comma delimited string.
                    var preids = prereqids.split(',');
                    console.log(modlist[i],': preids:',preids); 
        
                    for(var pid=0; pid<preids.length; pid++) {
                        //var itm = $.grep(modobjs, function(elem,index){
                        //  return elem.module_id == preids[pid];
                        //});
                        //console.log('prename:',itm[0].name);
                        //if(itm.length>0) { prename += itm[0].name; }
                        if(pid>0) { prename+=' & '; }
                        prename += preids[pid];// maybe just id
                    }
        
				modbox +='<div class="locked" data-toggle="tooltip" data-placement="bottom" title="'+prename+'"><i class="icon-lock"></i></div>';   
                }
                
				modbox +='<div class="items">'+modlist[i].module_id+' Items: '+modlist[i].items_count+'</div>';// testing
                       
                if(role == 'Learner') {
                    // if Learner calc filled stars from submissions
                    var starset = getStars(modlist[i].module_id);//find items
                    modbox += starset;
                } else {
                    modbox +='<div class="stars"><i class="icon-star"></i><i class="icon-star"></i><i class="icon-star-half-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';
                }
                
                // + cog edit { image upload? }
                modbox +='</div>';
                
				widt += 190;// add for each moditem
                $(nuplace).css({ 'width': widt});
				//$(nuplace).parent().css({'overflow': 'hidden' });// id=tab_# class=tab-pane
                //console.log('width:',widt);
                
                $(nuplace).append(modbox);
            }
        }
    }
    
    //console.log('modobjs:', modobjs);// to search from
    
    //bootstrap nav-tabs already active
/*	$('#tabs a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});*/
	// activate first tab
	$('#tabs a:first').tab('show');
    
    //if component width > any single tab width, turn off scroll arrows.
    arrowsNeeded();
    function arrowsNeeded() {
        var docWidth = $( document ).width();
        var needArrows=false;
        //var nuplace = $('#tab_'+c+'body');
        for(var i=0; i<tabCounter; i++) {
            if($('#tab_'+i+'body').width() > docWidth) { needArrows=true; }
        }
        if( !needArrows ) {
            $('.arol, .aror').hide();
        }
    }
    
    function addTab(tabname) {
        var label = tabname;
            id = "tab_" + tabCounter,
            li = $( tabTemplate.replace( /#\{href\}/g, "#" + id ).replace( /#\{label\}/g, label ) ),
            tabContentHtml = "<div id='" + id + "' role='tabpanel' class='tab-pane'>";
			tabContentHtml +="<div id='"+id+"body' class='tabody'></div></div>";
        
        $('#tablist').append(li);
        $('#tabdy').append(tabContentHtml);
        tabCounter++; 
    }
    

    /* click module */
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
		//append prerequisite_module_ids 
		if(mod[0].prerequisite_module_ids != '') {
			var prereqids=mod[0].prerequisite_module_ids;
            var prename = '';
            var preids = prereqids.split(',');
            for(var pid=0; pid<preids.length; pid++) {
                var itm = $.grep(modobjs, function(elem,index){
                    return elem.module_id == preids[pid];
                });
                
                if(itm.length>0) { prename = itm[0].name; }
                //console.log('pid:',pid,'prename:',prename);
			var prereq = '<div class="prereq">';
				prereq +='<i class="icon-exclamation"></i> ';
				// stupid First is not here!
				prereq +='Prerequisite: '+preids[pid]+'-'+prename+'</div>';
                
                $('#detailed-body').append(prereq);//.append('<hr/>');
            }
            
		}
        for(var i=0; i<moditems.length; i++) {
			
            var item='<div class="assignment">';
            var ico = getIcon(moditems[i].type);
                item +=ico;//'<i class="icon-file-text"></i> ';
				item +=' Type: '+moditems[i].type;// determine icon    
//test: link sends module id, item content_id to find submission
item+=' <a href="javascript:void(0);" onClick="findRelation('+modid+','+moditems[i].content_id+');"> '+moditems[i].title+'</a>';
                
                //item+=' <a target="_blank" href="'+moditems[i].html_url+'" target="_blank">'+moditems[i].title+'</a>';
				//item+='<a target="_blank" href="'+moditems[i].html_url+'?module_item_id='+moditems[i].module_item_id+'" target="_blank">'+moditems[i].title+'</a>';
				if(moditems[i].content.length > 0) {
                    if(moditems[i].content[0].points_possible > 0){
                        item+=' worth: '+moditems[i].content[0].points_possible;
                    }
					item+=' '+moditems[i].content[0].lock_explanation;
                }
                item+='</div>';
            $('#detailed-body').append(item);
        }
        // trigger modal
        $('#detailed-title').html(mod[0].name);
        $('#itemdetails').modal('show');
    });

    // scroll 1 module item per hover and click
    $('.aror').on('mouseenter click', function(){
        var activeTab = $('div.active').attr('id');//console.log('rightscroll:',activeTab);
        var sat = $('#'+activeTab).scrollLeft();
        $('#'+activeTab).animate({ scrollLeft:sat+200 });
    });
    $('.arol').on('mouseenter click', function(){
        var activeTab = $('div.active').attr('id');
        var sat = $('#'+activeTab).scrollLeft();
        $('#'+activeTab).animate({ scrollLeft:sat-200 });
    });

function getIcon(type) {
    //console.log('type:',type);
    var ico = 'icon-book';
    
    switch(type){
        case 'Assignment':
            ico='icon-pencil-square';
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

        if submission
            earned = each item subm score

    5 stars = percent completed
    each star = 20% round to nearest 10%
    filled=20, half=10, open=0

    return stars config
*/
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
    
    var starset = '<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';//no score
    
    if(score > 0) {
        console.log(modid, 'score:',score, 'total:',total);
        // calc percent (ex: 15/70, 30/65 )
        var percent = (score/total) *100; //console.log('percent=',percent);
        // build star set
        starset='<div class="stars">';
        for(var s=1; s<6; s++) {
            // 1-10 ?
            
            
            
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
    
/**** TEST ****/  
/* module item clicked sends module id, module_item id
    first find module item
 see if it matches a submission
 item.title == assignments.name 
    assignment_id == submissions.assignment_id
*/
	findRelation= function(mod,item) {
		console.log('modobj',mod,'content_id:',item);
		//console.log('module_item_id:',id);
		//console.log('module item:',id);
		if(role=='Learner') {
			var mod1 = $.grep(modobjs, function(elem,index){
				return elem.module_id == mod;
			});
			console.log('mod1:',mod1);
			
			var item1 = $.grep(mod1[0].module_items, function(elem,index){
				return elem.content_id == item;
			});
			console.log('item1:',item1);
		//get title, match assignment, match assgnid with submissions assgnid
			var title=item1[0].title;
            console.log('item1.title:',title);
			var asgn1 = $.grep(assignments, function(elem,index){
				//return elem.assignment_id == id;
				return elem.name == title;
			});// if type:quiz quiz_id ?
			
			console.log('asgn1:',asgn1);
			
			if(asgn1.length>0){
				
                var anid = asgn1[0].assignment_id;
                console.log('asgn1.assignment_id:',anid);
                var subm1 = $.grep(subms, function(elem,index){
                    return elem.assignment_id == anid;
                });//grader_id?
                console.log('subm1:',subm1);
			}
		}
	} 
    

/* not needed, used tooltip to show prerequisite_module_ids
Figure out Locked
	$('.locked').on('mouseenter', function(e){
		var pid = $(e.currentTarget).parent().attr('id');
		console.log('p.id:',pid);
	});
*/
// test: deep search for module children
/*
    var chmods = [];
    findNested(moduledata[0].children, 'children', chmods );
    function findNested(obj, key, memo) {
      var i,
          proto = Object.prototype,
          ts = proto.toString,
          hasOwn = proto.hasOwnProperty.bind(obj);

      if ('[object Array]' !== ts.call(memo)) memo = [];

      for (i in obj) {
        if (hasOwn(i)) {
          if (i === key) {
            memo.push(obj[i]);
          } else if ('[object Array]' === ts.call(obj[i]) || '[object Object]' === ts.call(obj[i])) {
            findNested(obj[i], key, memo);
          }
        }
      }

      return memo;
    }
console.log('chmods:',chmods);
*/
/*
//http://stackoverflow.com/questions/15642494/find-property-by-name-in-a-deep-object
//this will deep search an array of objects (hay) for a value (needle) then return an array with the results.
    var cnmods = [];// 0
    search(moduledata[0], 'children', cnmods);
    //search = function(moduledata, 'children', cmods) {
    function search(hay,needle,accumulator) {
      var accumulator = accumulator || [];
        //console.log('type: '+typeof hay);// object,number or string
      if (typeof hay == 'object') {
        for (var i in hay) {
          search(hay[i], needle, accumulator) == true ? accumulator.push(hay) : 1;
        }
      }
      //return new RegExp(needle).test(hay) || accumulator;
        return accumulator;
    }
   console.log('cnmods:', cnmods.length, cnmods); 
*/
    
//http://stackoverflow.com/questions/15523514/find-by-key-deep-in-nested-json-object
    var cmods = [];
    getChildren(moduledata);// close: refine this
    
    function getChildren(theObj) {
        var result = null;
        for(var i=0; i<theObj.length; i++) {
            var cobj = theObj[i].children;
            if(cobj.length > 0) {
                console.log('children: '+cobj.length);
                cmods.push(theObj[i].children);// to global
                result = getChildren(theObj[i].children);
                if(result) { break; }
            } 
        }
        return result;
    }
    console.log('cmods:', cmods.length, cmods);
    
});