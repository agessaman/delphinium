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
            var leng = mods.length;
            var modlist = [];// modules in this tab
    //do this in Modulemap after getModules in another function ?
            for(var i=0; i<leng; i++) {
                // individual module objects for searching items
                modobjs.push(mods[i]);// all
                modlist.push(mods[i]);// modules in this tab
                // if module has children add it
                if(mods[i].children.length > 0 ) {
                    var len = mods[i].children.length;
                    console.log(mods[i].name+' children: '+len);
                     
                    //DEEP CHILDREN: $.each(  http://api.jquery.com/category/utilities/
                    
                    //insert into other array
                    for(var mc=0; mc<len; mc++) {
                        // & child of child of child ?
                        modobjs.push(mods[i].children[mc]);
                        modlist.push(mods[i].children[mc]);
                    }
                }
            }
            // then display array
            for(i=0; i<modlist.length; i++) {
                //Module box with title & image
            var modbox = '<div id="'+modlist[i].module_id+'" class="moditem" data-locked="'+modlist[i].locked+'">';
                modbox +='<div class="title">'+modlist[i].name+'</div>';
                
				modbox +='<div class="locked" data-toggle="tooltip" data-placement="bottom" title="'+modlist[i].prerequisite_module_ids+'">L:'+modlist[i].locked+'</div>';//testing
				
				modbox +='<div class="items">'+modlist[i].module_id+' Items: '+modlist[i].items_count+'</div>';// testing
                // progress bar or stars
				//modbox +='<div class="stars">5 stars here</div>';
				modbox +='<div class="stars"><div class="progress-label">0%</div></div>';
				// if Learner fill progress bar

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
    
	//all progress bars// jqui : Err: not a function
	// works in instructor.htm but not built yet
	//$('.stars').progressbar({ value: false });
	// had this same problem in poppies!
	if(role == 'Learner') {
		//for each modobjs calculate % complete from submissions
	}
	
    //bootstrap nav-tabs already activated
/*	$('#tabs a').click(function (e) {
	  e.preventDefault();
	  $(this).tab('show');
	});*/
	// activate first tab
	$('#tabs a:first').tab('show');
   
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
			var preid=mod[0].prerequisite_module_ids;
			var prer = $.grep(modobjs, function(elem,index){
				return elem.module_id == preid;
			});
			if(prer.length > 0) { preid = prer[0].name; }
			console.log('preid:',preid,'prer:',prer);
			var prereq = '<div class="prereq">';
				prereq +='<i class="icon-exclamation"></i> ';
				// stupid First is not here!
				prereq +='Prerequite: '+preid+'</div>';
				
			// get name of prereq & ??
			$('#detailed-body').append(prereq).append('<hr/>');
		}
        for(var i=0; i<moditems.length; i++) {
			// style by moditems.type? with icon?
            var item='<div class="assignment">';// create one still
                //item+='<a target="_blank" href="'+moditems[i].html_url+'?module_item_id='+moditems[i].module_item_id+'" target="_blank">'+moditems[i].title+'</a>';
                item +='<i class="icon-file-text"></i> ';
				item+='<a target="_blank" href="'+moditems[i].html_url+'" target="_blank">'+moditems[i].title+'</a>';
                if(moditems[i].content.length > 0) {
                    item+=' worth: '+moditems[i].content[0].points_possible;
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