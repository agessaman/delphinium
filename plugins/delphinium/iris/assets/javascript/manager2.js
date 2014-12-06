$(document).ready(function() {  
    console.log(moduleData);
//to prevent the form from submitting on "Enter"
  $(window).keydown(function(event){
    if(event.keyCode == 13) {
      event.preventDefault();
    }
  });
  
  var li = d3.select("#nestable").append("ol").selectAll("li")
        .data(moduleData[0].children)
        .enter().append("li")
        .attr("id", function(d) { return "path"+d.moduleId; })
        .attr("class","dd-item dd3-item li-parent")        
        .attr("data-id",function(d){
            return d.moduleId;
         })
         .attr("id",function(d){
            return "li" + d.moduleId;
         });
    
    
    li.append("div")
     .attr("class", "dd-handle dd3-handle");
   
   
    
  li.append("i")
    .attr("class", "icon-sort-down dive")
    .on("click", function(d)
    {
    });

    var content = d3.select(this.parentNode).insert("div","ol")
    .attr("class","module-content");

    li.append("div")//any content must be added to this div
     .attr("class", "dd3-content")
     .attr("id",function(d){
        return "content-"+d.moduleId;
     })
    .text(function(d){
    	return d.name;
     })
    .on("click", function(d)
    {
        // Get the id name
        var parent = d3.select(this.parentNode);
        //console.log(parent);
        //console.log('sasdf');
        var id = parent.attr("data-id");

        //attach a new input box
        var input = parent.append("input")
        .attr("id", "text"+id)
        .attr("value",$("#content-"+id).html());
        //.attr("class","dd3-content");
        $("#text"+id).focus();

        input.on("keyup", function(e)
        {
            
            var moduleId = id;
            var inputObj = this;
            var keyValueParams = {name:this.value};
            if (d3.event.keyCode === 13) { //user hit enter -  submit changes
                jQuery.ajax({
                    url: "updateModule",
                    type: "POST",
                    data: {moduleId: moduleId,keyValueParams:JSON.stringify(keyValueParams)},
                    success: function (data) {  
                        //remove the input
                        deleteEditableInput(inputObj);
                        $("#content-"+moduleId).html(inputObj.value);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });

            }
            else if(d3.event.keyCode === 27)
            {
                //hit escape. Remove the item
                deleteEditableInput(inputObj);
            }
        });
    	
        
    });

console.log("here");
var items = moduleData[0].children.items;
    content.selectAll("div")
        .data(moduleData[0].children.items)
        .enter().append("div")
        .attr("class","divContent");
//    var data = d.items;
//    $.each(data, function( i, value ) {
//
//    //get icon for this assignment type
//    var icon = getIconForContentType(data[i]['type']);
//
//    var div = content.append("div")
//            .attr("class","divContent");
//
//    if(data[i]['html_url']===undefined)
//    {
//        div.append("i")
//            .attr("class",icon + " contentType");
//
//        div.append("span")
//            .html(data[i]['title']);
//    }
//    else
//    {
//        var link = div.append("a").attr("xlink:href", function(d)
//        {
//            return data[i]['html_url'];
//        })
//            .attr("class","contentLink");
//
//        link.append("i")
//            .attr("class",icon + " contentType");
//
//        link.append("span")
//            .html(data[i]['title']);
//    }
//
//    var currentTags;
//    var curData = data[i];
//    var contentId = curData['content_id'];
//    div.append("i")
//    .attr("class", "icon-tags dive")
//
//        
//  
//  
  
  
  
  
  
  /*
    //var div = d3.select("#nestable");
    var li = addLiItem(moduleData, "nestable");
    
    $("#nestable").nestable({});
    $('#nestable').on('change', function() {
    var list = $('#nestable').nestable('serialize');
    var json = JSON.stringify(list);
     
console.log(json);
    //TODO: parameterize the courseId
    $.post("saveModules",
            {courseId: 343331,modulesArray:json}, 
            function(data)
            {
                    console.log(data);
//                    console.log("success");
            })
            .fail(function(data) {
                    console.log(data);
//                    console.log("failed");
            });
    });
    */
});





function addLiItem(data, olsParentId)
{
    var selector = "#"+olsParentId;
    
    var parent = d3.select(selector);
    var olElement = parent.append("ol")
            .attr("class", "dd-list");
    
    var li = olElement.selectAll("li")
     .data(data)
     .enter()
     .append("li")
     .attr("class", "dd-item dd3-item li-parent")
     .attr("data-id",function(d){
        return d.moduleId;
     })
     .attr("id",function(d){
        return "li" + d.moduleId;
     });
    
    
    li.append("div")
     .attr("class", "dd-handle dd3-handle");
   
   
    
  li.append("i")
    .attr("class", "icon-sort-down dive")
    .on("click", function(d)
    {
        
        //TODO reset this button
//        this.className = "";
        
        //this.attr("class", "icon-sort-up dive");
        d3.event.preventDefault();
        var content = d3.select(this.parentNode).insert("div","ol")
        .attr("class","module-content");

         
                var data = d.items;
                $.each(data, function( i, value ) {
                    
                    //get icon for this assignment type
                    var icon = getIconForContentType(data[i]['type']);
                    
                    var div = content.append("div")
                            .attr("class","divContent");
                    
                    if(data[i]['html_url']===undefined)
                    {
                        div.append("i")
                            .attr("class",icon + " contentType");

                        div.append("span")
                            .html(data[i]['title']);
                    }
                    else
                    {
                        var link = div.append("a").attr("xlink:href", function(d)
                        {
                            return data[i]['html_url'];
                        })
                            .attr("class","contentLink");

                        link.append("i")
                            .attr("class",icon + " contentType");

                        link.append("span")
                            .html(data[i]['title']);
                    }
                    
                    var currentTags;
                    var curData = data[i];
                    var contentId = curData['content_id'];
                    div.append("i")
                    .attr("class", "icon-tags dive")
                    .on("click", function(curData)
                    {
                        
//                        jQuery.ajax({
//                            url: "getTags",
//                            type: "GET",
//                            data: {contentId:contentId},
//                            success: function (data) {
//                            
                                //the value is returned as a csv. Convert to array
                                currentTags = data.split(",");
                            //get this item's tags
                            var tagDiv = div.append("div")
                                    .attr("class","divTags")
                                    .attr("id","divTags");
    //                                .append("span")
    //                                        .text("Separate tags with a comma. Example: Quiz, Peer Review, Discussion");;

                            var wrap = tagDiv.append("div")
                                    .attr("class","wrap");
                            var allTags = tagDiv.append("div")
                                    .attr("class","tagWrap");
                            
                            var input = wrap.append("input")
                                    .attr("id", "tag"+d.moduleId);
                            wrap.append("button")
                                    .text("Add tags")
                                    .on("click",function(d)
                                    {

                                        d3.event.preventDefault();
                                        var tagValue = d3.select("#tag"+d.moduleId).property("value");

                                        var tagArr = tagValue.split(",");
                                        
                                        for(var i=0;i<=tagArr.length-1;i++)
                                        {
                                            tagArr[i] = tagArr[i].trim();
                                        }
                                        
                                        jQuery.ajax({
                                        url: "addTags",
                                        type: "POST",
                                        data: {contentId:contentId, tags:JSON.stringify(tagArr), courseId:courseId},
                                        success: function (data) {
                                            console.log(data);
                                        },
                                        error: function(data){
                                            console.log(data);
                                        }

                                    });
                                });

                                $.each(currentTags, function( i, value ) {
                                    if(currentTags[i].length>0)
                                    {
                                        var eachTag = allTags.append("div");

                                    eachTag.append("span")        
                                            .text(currentTags[i]);
                                    eachTag.append("i")
                                            .text("X")
                                            .on("click",function(d)
                                            {
                                                var tagToRemove = currentTags[i];
                                                var trimmed = tagToRemove.trim();
                                                console.log("tag to remove:'"+trimmed+"'");
                                                
                                                //remove the current tag
                                                jQuery.ajax({
                                                    url: "deleteTag",
                                                    type: "POST",
                                                    data: {contentId:contentId, tag:trimmed},
                                                    success: function (data) {
                                                        console.log("success");
                                                        var parent = d3.select(this.parentNode);
                                                        parent.remove();
                                                    },
                                                    error: function(data){
                                                        console.log(data);
                                                    }
                                                });
                                                
                                                
                                            });
                                    }
                                    
                                });
//                            },
//                            error: function(data){
//                                console.log(data);
//                            }
//                        });
//                        
                        
//                            $("#divTags").dialog();
                    });
                });
                

    });
    
    
    
   li.append("div")//any content must be added to this div
     .attr("class", "dd3-content")
     .attr("id",function(d){
        return "content-"+d.moduleId;
     })
    .text(function(d){
    	return d.name;
     })
    .on("click", function(d)
    {
        // Get the id name
        var parent = d3.select(this.parentNode);
        //console.log(parent);
        //console.log('sasdf');
        var id = parent.attr("data-id");

        //attach a new input box
        var input = parent.append("input")
        .attr("id", "text"+id)
        .attr("value",$("#content-"+id).html());
        //.attr("class","dd3-content");
        $("#text"+id).focus();

        input.on("keyup", function(e)
        {
            
            var moduleId = id;
            var inputObj = this;
            var keyValueParams = {name:this.value};
            if (d3.event.keyCode === 13) { //user hit enter -  submit changes
                jQuery.ajax({
                    url: "updateModule",
                    type: "POST",
                    data: {moduleId: moduleId,keyValueParams:JSON.stringify(keyValueParams)},
                    success: function (data) {  
                        //remove the input
                        deleteEditableInput(inputObj);
                        $("#content-"+moduleId).html(inputObj.value);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });

            }
            else if(d3.event.keyCode === 27)
            {
                //hit escape. Remove the item
                deleteEditableInput(inputObj);
            }
        });
    	
        
    });
    
    
    //RECURSION!!!

    var childrenData;
    var innerOl = li
        .filter(function(d)
            { 
                if(d.children===undefined)
                {
                    
                }
                else
                {
                    addLiItem(d.children, this.id);
                }
//                
            });
            
            
            //load the rest of the content
            loadContent();
}

function buildModuleItemsContent()
{
    
}

function deleteEditableInput(inputObj)
{
    inputObj.remove();
}

function detailedModuleView(sth)
{
    console.log(sth);
    
}

function getIconForContentType(contentType)
{
    //'File', 'Page', 'Discussion',
  //'Assignment', 'Quiz', 'SubHeader', 'ExternalUrl', 'ExternalTool'
    switch(contentType) {
    case 'File':
        return "icon-file";
    case 'Page':
        return "icon-file";
    case 'Discussion':
        return "icon-comment";
        
    case 'Assignment':
        return "icon-pencil-square-o";
    case 'Quiz':
        return "icon-question-circle";
    case 'SubHeader':
        return "icon-tasks";
    case 'ExternalUrl':
        return "icon-link";
    case 'ExternalTool':
        return "icon-external-link";
    default:
        return "icon-certificate";
    }
}

function loadContent()
{
    
}