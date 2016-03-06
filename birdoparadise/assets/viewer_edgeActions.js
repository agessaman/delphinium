/***********************
* Adobe Edge Animate Composition Actions
*
* Edit this file with caution, being careful to preserve 
* function signatures and comments starting with 'Edge' to maintain the 
* ability to interact with these actions from within Adobe Edge Animate
*
***********************/
(function($, Edge, compId){
var Composition = Edge.Composition, Symbol = Edge.Symbol; // aliases for commonly used Edge classes

   //Edge symbol: 'stage'
   (function(symbolName) {
      
      
      Symbol.bindElementAction(compId, symbolName, "document", "compositionReady", function(sym, e) {
         // insert code to be run when the composition is fully loaded here
         
         /* 
         XML node
         	<module num='1'></module>
         	<filename>buildarack/index.html</filename>
         	<thumbimage>images/buildarack_thum.png</thumbimage>
         	<title>Build A Rack</title>
         	<info>long description</info>
         */
         
         // for mobile?
         var mainpath='https://development.flyuvu.com/external_media/edge/';//+lessonpath + index.html
         var xmlfilename='lessonlist.xml';
         
         // symbols
         var mainStage = sym.$('Stage');// for dialogBox
         
         // set from xml
         var lessontitle='Header Title';//<maintitle>
         var titlecolor='';//<headtextcolor>
         var textcolor = '#FFFFFF';// in scroller & thumbs
         var bkgrndcolor='#FFFFFF';
         var thumbkgcolor='#FFFFF';
         
         var taskArray=[];// holds xml as array REPLACE W/ XMLDATA
         var thumbtns=[];// holds clickable thumbnails with info
         var xmldata='';// holds xml
         
         
         // BEGIN ********************
         
         loadTheXML();// then buildpage
         
         function loadTheXML()
         {
         	$.ajax({type:'GET', 
         			url:xmlfilename,
         			dataType:'xml', 
         			cache: false,
         			error: function(e,ts,et) { console.log('ERROR loading xml: '+et+' : '+ts); },
         			success: function(xml)
         			{
         			/*
         				lessontitle = $(xml).find('maintitle').text();
         				titlecolor = $(xml).find('headtextcolor').text();
         				textcolor = $(xml).find('textcolor').text();
         				bkgrndcolor = $(xml).find('bkgrndcolor').text();
         				thumbkgcolor = $(xml).find('thumbkgcolor').text();
         				*/
         				xmldata = xml;// global version of xml file
         				sym.buildpage();
         			}
         	});// end GET
         }
         
         // construct the page
         sym.buildpage=function()
         {
         	console.log('buildpage');
         	///lessontitle = $(xml).find('maintitle').text();
         	titlecolor = $(xmldata).find('headtextcolor').text();
         	textcolor = $(xmldata).find('textcolor').text();
         	bkgrndcolor = $(xmldata).find('bkgrndcolor').text();
         	thumbkgcolor = $(xmldata).find('thumbkgcolor').text();
         	
         	mainStage.css({'background-color':bkgrndcolor});
         	sym.$('maintitle').html($(xmldata).find('maintitle').text());
         	sym.$('maintitle').css({'color':titlecolor});// <headtextcolor> ?
         
         	var btncount = $(xmldata).find('lessonitem').length;// 12 total
         	console.log('total btncount='+btncount);
         		
         	//var num=0;// first scroller at 12,60 : width=1000 height=222
         	var pageleft=12;
         	var pagetop=60;
         	// for each moduletitle num add a scroller
         	var bars = $(xmldata).find('moduletitle').length;
         	console.log('create '+bars+' scrollers');
         	
         	var indx=0;//thumbtns[index]
         	
         	for(var b=1; b<=bars; b++)
         	{
         		var sbx1 = sym.createChildSymbol('scroller',mainStage);
         		sbx1.getSymbolElement().css({'left':pageleft,'top':pagetop});
         		
         		var scr1 = sbx1.$('scrollbox');
         		//<moduletitle num='1'>Module 1 Hardware</moduletitle>
         		var modtitle = $(xmldata).find("moduletitle").eq(b-1).text();
         		sbx1.$('scrolltitile').html(modtitle);
         		console.log('b='+b+' mod title='+modtitle);
         		//pagetop += 30;//auto 
         		
         		// for each thumbnail add a thumbnail symbol
         		var bl=0;
         		var bt=2;
         		
         		$(xmldata).find('lessonitem').each(function(){
         		
         			var num = $(this).find('module').attr('num');
         			if(num == b){ 
         			console.log('add thumb for '+num+' title='+$(this).find('title').text());
         			//}
         		
         			var tbtn = sym.createChildSymbol('thumbnail',scr1);
         			// use xml instead of taskArray
         			tbtn.$('thumimage').append('<img src="'+$(this).find('thumbimage').text()+'" width="200" height="123" />');
         			tbtn.$('thumtitle').html($(this).find('title').text());
         			
         			//taskArray['completed']
         			tbtn.$('checkmark').hide();// marks viewed NOT SEEN UPON RETURN
         
         			tbtn.getSymbolElement().css({'position':'absolute','left':bl,'top':bt});
         			tbtn.$('thumbkg').css({'background-color':thumbkgcolor});
         			tbtn.$('thumtitle').css({'color':textcolor});
         			
         			// setVariable('completed',yesno replaces taskArray['completed'] USABLE???
         			tbtn.setVariable('filename',$(this).find('filename').text());
         			tbtn.setVariable('btnum',indx);
         			//$(tbtn).bind('click', sym.thumbclicked);// process object clicked
         			//tbtn.click=sym.thumbclicked;
         			thumbtns.push(tbtn);
         			indx++;//array index
         			bl += 240;// 240=thumbnail.width() no spaces between
         			}
         			//end if num==b
               });
         		//JQ.UI.draggable set limits with [x1, y1, x2, y2] or 'parent', 'document', 'window'
         		var sliderwidth = 1000;//Math.floor(sbx1.getSymbolElement().width());// constant 1000
         		console.log('sliderwidth='+sliderwidth+' bl='+bl);// bl varies
         		var sleft = bl-sliderwidth;
         		var dragcontainer=[-sleft,0,24,0];
         		console.log('dragcontainer='+dragcontainer); 
         		if(bl>sliderwidth)
         		{
         			scr1.css({'cursor':'ew-resize'});
         			$(scr1).draggable({ axis:'x',containment:dragcontainer });
         		}  // jquery.ui.touch-punch.js worked for ipad
         	}
         }
         
         ///sym.thumbclicked=function(event)
         // sends the btnum set at createChild from symbol
         sym.thumbclicked=function(btnum)
         {
         	console.log('thumbtn clicked');
         	console.log('tbtn['+btnum+'] filename='+thumbtns[btnum].getVariable('filename'));
         	thumbtns[btnum].$('checkmark').show();
         	// launch mainpath+filename in a same view in APP cannot layer stageWebViews in air
         	// maybe do a java version for android >
         	location.href = thumbtns[btnum].getVariable('filename');
         	// in exitbtn add: window.history.back();
         	/*
         	// or popup browser window w/ filename // 
         	var nuwindow=window.open(thumbtns[btnum].getVariable('filename'), taskArray[btnum]['title'],'height=768,width=1024');
         	if(window.focus) { nuwindow.focus(); }
         	*/
         }

      });
      //Edge binding end

      Symbol.bindSymbolAction(compId, symbolName, "beforeDeletion", function(sym, e) {
         // insert code to be run just before a symbol is deleted here
         // xml nodes
         /*
         
         <?xml version="1.0" encoding="UTF-8"?>
         <xmlfile>
         	<maintitle>NIDS DEMO Lesson Viewer</maintitle>
         	<moduletitle num='1'>Module 1 NIDS Hardware</moduletitle>
         	<moduletitle num='2'>Module 2 NIDS Software</moduletitle>
         	<moduletitle num='3'>Module 3 Corrective Maintenance</moduletitle>
         	
         	<headtextcolor>#1B1B1B</headtextcolor>
         	<textcolor>#1B1B1B</textcolor>
         	<bkgrndcolor>#BBBBBB</bkgrndcolor>
         	<thumbkgcolor>#DDDDDD</thumbkgcolor>
         
         	<lessonitem>
         		<module num='1'></module>
         		<filename>buildarack/index.html</filename>
         		<thumbimage>images/buildarack_thum.png</thumbimage>
         		<title>Build A Rack</title>
         		<info><![CDATA[
         <p>Longer Description of the lesson???</p>
         		]]></info>
         	</lessonitem>
         
         	<lessonitem>
         		<module num='2'></module>
         		<filename>configuration/index.html</filename>
         		<thumbimage>images/configuration_thum.png</thumbimage>
         		<title>Software Configuration</title>
         		<info><![CDATA[
         <p>Longer Description</p>
         		]]></info>
         	</lessonitem>
         
         </xmlfile>
         */

      });
      //Edge binding end

   })("stage");
   //Edge symbol end:'stage'

   //=========================================================
   
   //Edge symbol: 'thumbnail'
   (function(symbolName) {   
   
      Symbol.bindElementAction(compId, symbolName, "${_hitbox}", "click", function(sym, e) {
         
         var btnum = sym.getVariable('btnum');
         ///console.log('btnum='+btnum);
         sym.getComposition().getStage().thumbclicked(btnum);

      });
      //Edge binding end

   })("thumbnail");
   //Edge symbol end:'thumbnail'

   //=========================================================
   
   //Edge symbol: 'scroller'
   (function(symbolName) {   
   
   })("scroller");
   //Edge symbol end:'scroller'

})(jQuery, AdobeEdge, "EDGE-21465918");