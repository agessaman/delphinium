$(document).ready(function() {
	/* Add the content message dynamically, could be different for role */
	$('#popinfo').attr('data-content','Click a module to see assignments');
    $('#popinfo').popover();// activate info
    /*
        Bird of Paradise displays modules in tabbed sections 
        configure the Tab/modules structure with Stem
        
        http://getbootstrap.com/components/#nav
        http://daftspunk.github.io/Font-Autumn/
        
        todo:
        Tooltip or popover for reason locked prerequisites
		module box background images - upload and location
        assignment links in locked modules are disabled for student view
    */
	var stateColors = {locked: "#8F8F8F", unlocked: "#588238", started: "#5eacd4", completed: "#143D55"};
    //var backColors = {locked: "#DDDDDD", unlocked: "#588238", started: "#5eacd4", completed: "#133D55"};
    // colors from iris
    
    var tabCounter = 0;
    var tabTemplate = "<li role='presentation'>";
		tabTemplate +="<a role='tab' data-toggle='tab' href='#{href}' aria-controls='#{href}'>#{label}</a>";
        tabTemplate +="</li>";
    // display module items when modbox clicked
    var modobjs = [];// all modules for search
    var modlist = [];// modules in this tab
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
            modlist = [];// modules in this tab
            // deep search children
			getMyChildren(chld[c]['children']);
            // now display modlist for each tab
            for(i=0; i<modlist.length; i++) {
                //Module box with title, lock, stars & image
            var modbox = '<div id="'+modlist[i].module_id+'" class="moditem" data-locked="'+modlist[i].locked+'">';
                modbox +='<div class="title">'+modlist[i].name+'</div>';
                //console.log('locked:',modlist[i].state);//null,locked,unlocked,started,completed
                if(modlist[i].state == 'locked') {
                    var prereqids = modlist[i].prerequisite_module_ids;
                    var prename = '';// can have multiple. comma delimited string.
                    var preids = prereqids.split(',');
                    //console.log(modlist[i],': preids:',preids); 
        
                    for(var pid=0; pid<preids.length; pid++) {
                        if(pid>0) { prename+=' & '; }
                        prename += preids[pid];// maybe just id
                    }
				    modbox +='<div class="locked" data-toggle="tooltip" data-placement="bottom" title="'+prename+'"><i class="icon-lock"></i></div>';   
                }
				//modbox +='<div class="items">'+modlist[i].module_id+' Items: '+modlist[i].items_count+'</div>';// testing
                if(role == 'Learner') {
                    // if Learner calc filled stars from submissions
                    var starset = getStars(modlist[i].module_id);//find items
                    modbox += starset;
                } else {
                    // Instructor no scores available
                    modbox +='<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';
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
    
	// activate first tab
	$('#tabs a:first').tab('show');
    
    /* if component width > any tab body width, turn off scroll arrows
       if browser is resized, check if arrows are needed */
    $( window ).on('resize', function() { arrowsNeeded(); });
    arrowsNeeded();
    function arrowsNeeded() {
        var docWidth = $( document ).width();
		var parentWidth = $('.page').parent().width();
        var needArrows=false;
        for(var i=0; i<tabCounter; i++) {
			var tabWidth = $('#tab_'+i+'body').width();
            if(tabWidth > docWidth) { needArrows=true; }
			if(tabWidth > parentWidth) { needArrows=true; }
        }
        if(needArrows) {
            $('.arol, .aror').show();
        } else { $('.arol, .aror').hide(); }
    }

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
		//append prerequisite_module_ids 
		if(mod[0].prerequisite_module_ids != '') {
			var prereqids=mod[0].prerequisite_module_ids;
            var prename = '';
            var preids = prereqids.split(',');
            
            var prereq = '<div class="prereq">';
				prereq +='<div class="ico"><i class="icon-exclamation-triangle"></i> Module Prerequisites<br/></div>';// orange
                prereq +='<div class="prereqnote">Before you can start this module you need to complete the following modules:</div>';
                prereq +='<div class="clearme"></div>';
            
            for(var pid=0; pid<preids.length; pid++) {
                var itm = $.grep(modobjs, function(elem,index){
                    return elem.module_id == preids[pid];
                });
                
                if(itm.length>0) { prename = itm[0].name; }
                //console.log('pid:',pid,'prename:',prename);
				prereq +='<div class="ico">'+preids[pid]+'-'+prename+'</div>';// orange
            }
            prereq +='<div class="clearme"></div>';
            prereq +='</div>';// close prereq

            $('#detailed-body').append(prereq);
		}
		
		//mod[0].state;//null,locked,unlocked,started,completed
        for(var i=0; i<moditems.length; i++) {
			var hasContent=false;
            var item='<div class="assignment">';
            var ico = getIcon(moditems[i].type);
            if(moditems[i].content.length > 0) { hasContent=true; }
            if(hasContent && moditems[i].content[0].points_possible > 0) {
                // before link & icon float:right
                item+='<div class="points">';
                if(role == 'Learner') {
                // if student 'Score: ##/possible'
                    //JUST .title
                    item += 'Score: '+getScore(moditems[i].title)+'/'+moditems[i].content[0].points_possible+'</div>';
                } else {
                    item+=moditems[i].content[0].points_possible+' pts.</div>';// close points
                }
            }
            item +='<div class="ico">'+ico+'</div>';//'<i class="icon-file-text"></i> ';
            //item +=' Type: '+moditems[i].type;// determine icon                    
            //item+='<a target="_blank" href="'+moditems[i].html_url+'" target="_blank"> '+moditems[i].title+'</a>';
            item +='<div class="link">';
			if(mod[0].state == 'locked') {
				item += ' '+moditems[i].title;// not a link if locked
			} else {
				item +='<a target="_blank" href="'+moditems[i].html_url+'?module_item_id='+moditems[i].module_item_id+'" target="_blank"> '+moditems[i].title+'</a>';
            }
			item +='</div>';
			if(moditems[i].completion_requirement.length > 0) {
                item += '<div class="required">'+moditems[i].completion_requirement+'</div>';
            }
            
            // after link
            if(hasContent && moditems[i].content[0].lock_explanation.length >0) {
                console.log('lock_ex len:',moditems[i].content[0].lock_explanation.length);
               item +='<div class="prereqnote">'+moditems[i].content[0].lock_explanation+'</div>';
            /* WEIRD CONTENT BROKE THIS !!! gets truncated in db !
			length=255 actualCONTENT.length = 366
            lock_explanation:
            "This quiz is part of the module <b>Organizational Controls</b> and hasn&#39;t been unlocked yet.
            <br/>
            <div class='spinner'></div>
            <a style='display: none;' class='module_prerequisites_fallback' href='https://uvu.instructure.com/courses/343331/modules#module"
            */
            }
            
            item +='<div class="clearme"></div>';
            item +='</div>';
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

    function getIcon(type) {
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
        // default: no score yet empty stars
        var starset = '<div class="stars"><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i><i class="icon-star-o"></i></div>';

        if(score > 0) {
            //console.log(modid, 'score:',score, 'total:',total);
            var percent = (score/total) *100;//console.log('percent=',percent);
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