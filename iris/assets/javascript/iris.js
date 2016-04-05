//GLOBAL VARIABLES
var rawSubmissionData;
var accessibleSubmissData = new Array();
var moduleStates;
var newModuleStates=[];
var submissionData;
var irisObj;
var totalCharts=0;

//TODO: make colors configurable
var stateColors = {locked: "#8F8F8F", unlocked: "#588238", started: "#5eacd4", completed: "#143D55"};
var backColors = {locked: "#DDDDDD", unlocked: "#588238", started: "#5eacd4", completed: "#133D55"};
var breadCrumb = [['width'], ['points'], ['name'], ['path']];
var b = {
    w: 94, h: 15, s: 2, t: 10
};

//will detect all the component instances and call createChart for each of them
$(window).load(function () {
    for (element in window) {
        if (element.indexOf('delphinium_iris')===0) {
            totalCharts++;
            createChart(window[element]);
        }
    }

    getStudentSubmissions();

});

function createChart(iris)
{
    irisObj = iris;
    graphData = iris.graphData[0];
//todo: calculate how big each svg needs to be
//get parent size
    var parent = $("#wrapper" + iris.filter).parent();

    var width = parent.width(),
        height = parent.height(),
        radius = Math.min(width, height) / 2;

    //if the parent did not specify a height and width, the chart will be 500x500
    if(width<1||height<1)
    {
        width = 500, height = 500, radius = Math.min(width, height) / 2;
    }
    var svg = d3.select("#wrapper" + iris.filter).append("svg")
        .attr("id", "svg" + iris.filter)
        .attr("width", width)
        .attr("height", height)
        .append("g")
        .attr("transform", "translate(" + width / 2 + "," + height * .5 + ")");

    var partition = d3.layout.partition()
        .sort(null)
        .size([2 * Math.PI, radius])
        .value(function (d) {
            return 1;
        });

    var arc = d3.svg.arc()
        .startAngle(function (d) {
            return d.x;
        })
        .endAngle(function (d) {
            return d.x + d.dx;
        })
        .innerRadius(function (d) {
            return d.y;
        })
        .outerRadius(function (d) {
            return d.y + d.dy;
        });

    var path = svg.datum(graphData).selectAll("path")
        .data(partition.nodes)
        .enter().append("path")
        .attr("id", function (d) {
            return "path" + d.module_id;
        })
        .attr("class", function (d) {
            return "path" + d.module_id;
        })
        .attr("d", arc)
        .on("mouseenter", function (d) {
            showTooltip(d);
        })
        .on("mousemove", function (d) {
            followCursor();
            highlightCurrentTree(d);
        })
        .on('mouseleave', function (d) {
            resetTreeOpacity();
            if (!$("#blocker").hasClass("show"))
            {
                d3.select("#tooltip").attr("class", "hidden");
            }
        })
        .on('click', function(d){
            modalBoxShow(d);
        })
        .style("stroke", "#fff")
        .style("fill", function (d) {
            return "#8F8F8F";
        })
        .style("fill-rule", "evenodd")
        .style("cursor", "pointer");

    d3.select("#stackImgClose")
        .on("mouseenter", function (d) {
            d3.select("#circle").classed("fa fa-circle-thin fa-stack-1x", true);
        })
        .on('mouseleave', function (d) {
            d3.select("#circle").classed("fa fa-circle-thin fa-stack-1x", false);
        });


    function showTooltip(d)
    {
        resetTooltipContent();
        var hasPrereqs = false;
        //make the title bar the same color to represent the state
        var backColor = backColors['locked'];

        if (newModuleStates !== undefined)
        {
            var ob = newModuleStates.filter(function (ob)
            {
                var id = parseInt(d.module_id);
                if (ob.module_id === id)
                {
                    return ob;
                }
            })[0];

            if (ob !== undefined) {
                var newColor = ob.color;
                var backColor =ob.color;
                d3.select(".titleBar").style("background-color", newColor);
//            d3.select("#imgClose").style("color", newColor);
            }

        }

        //PREREQUISITES
        if ((d.prerequisite_module_ids === undefined) || (d.prerequisite_module_ids < 1))
        {
            d3.select("#divPrerequisites").attr("class", "hidden");
        }
        else
        {

            d3.select("#divPrerequisites").attr("class", "visible");
            var prereqs = d.prerequisite_module_ids.split(", ");
            var actualPrereqs;
            if (moduleStates === undefined)
            {
                actualPrereqs = prereqs;
            }
            else
            {
                actualPrereqs = prereqs.filter(function (d) {

                    var obj = moduleStates.filter(function (obj)
                    {
                        var id = parseInt(d);
                        if (obj.module_id === id)
                        {
                            return obj;
                        }
                    })[0];
                    if (obj !== undefined)
                    {
                        return obj.state !== 'completed';
                    }
                    else//if the obj is undefined it means that the item that is a prerequisite is not published, so it's as if it were not a prereq
                    {//so we won't include it in the list of prerequisites
                        return false;
                    }
                });
            }


            if (actualPrereqs.length < 1)
            {
                d3.select("#divPrerequisites").attr("class", "hidden");
            }
            else
            {
                hasPrereqs = true;
                //check if the prereqs are completed; only show those that aren't
                var ulPrereqs = d3.select("#ulPrerequisites").selectAll("li")
                    .data(actualPrereqs)
                    .enter()
                    .append("li");


                var prereqObjArr = [];
                ulPrereqs.append("a")
                    .text(function (prereqs)
                    {
                        //search for the module names
                        var obj = rawData.filter(function (obj) {

                            //add each prereq to an array
                            if (obj.module_id === parseInt(prereqs))
                            {
                                prereqObjArr[obj.module_id] = obj;
                                return obj;
                            }
                        })[0];
                        if (obj !== undefined)
                        {
                            if (obj.name.length >= 85)
                            {
                                var subs = obj.name.substring(0, 85);
                                return subs += "...";
                            }
                            else
                            {
                                return obj.name;
                            }
                        }
                    });


//              Highlight prerequisite
                var originalColor = "";
                var prereqDoms = [];

                ulPrereqs
                    .on("mouseenter", function (prereqs)
                    {
                        prereqDoms = d3.selectAll(".path" + prereqs);
                        originalColor = prereqDoms.style("fill");
                        prereqDoms.style("fill", "#FF6600");
                        d3.select("#tooltip")
                            .attr("class", "seeThroughOnly");
                    })
                    .on("mouseleave", function (prereqs)
                    {
                        d3.select(this).style("color", "black");
                        d3.select("#tooltip")
                            .attr("class", "solid");

                        prereqDoms.style("fill", originalColor);
                    });
            }


        }


        breadCrumbFill(d);


        //Show the tooltip
        d3.select("#tooltip")
            .attr("class", "transparent");

        d3.select("#divInnerTooltip")
            .attr("class", "hideOverflow");

        //reset scroll
        var div = document.getElementById("divInnerTooltip");
        div.scrollTop = 0;
        div.scrollLeft = 0;


        //FILL OUT CONTENT (ASSIGNMENTS)
        var contentItems = d.module_items;
        if (contentItems !== undefined)
        {
            if(d.published!=="0")
            {
                var tooltip = d3.select("#tooltip");
                //register a mousemove event so the tooltip keeps following the mouse

                var tit = d.name;
                if (tit.length > 67)
                {
                    tit = tit.substring(0, 64) + "...";
                }
                tooltip.select("#spTitle")
                    .text(tit);

                var optionalTags = false;
                var requiredAssignments = [];
                var optTagsArr = [];
                for (var i = 0; i <= contentItems.length - 1; i++)
                {
                    var optTags = getTags(contentItems[i]);

                    if (optTags)
                    {
                        optionalTags = true;
                        optTagsArr.push(contentItems[i]);
                    }
                    else if ((contentItems[i].type === "Quiz") || (contentItems[i].type === "Assignment"))
                    {
                        requiredAssignments.push(contentItems[i]);
                    }
                }

                if (optionalTags)
                {
                    var completedArr = [];
                    //only display optional tags if required content has been completed
                    for (var i = 0; i <= requiredAssignments.length - 1; i++)
                    {
                        var currentAssignment = requiredAssignments[i];
                        //only display optional tags if required content has been completed
                        ob = accessibleSubmissData.filter(function (ob)
                        {
                            if (parseInt(ob.content_id) === parseInt(currentAssignment.content_id))
                            {
                                return ob;
                            }
                        });

                        if(ob.length>0)
                        {
                            completedArr.push(ob);
                        }
                    }

                    if (completedArr.length !== requiredAssignments.length)
                    {
                        for (var j = 0; j <= optTagsArr.length - 1; j++)
                        {
                            var parent = $("#content" + optTagsArr[j].content_id).parent();
                            var cnt = parent.contents();
                            parent.replaceWith(cnt);
                            var optTag = $("#content" + optTagsArr[j].content_id + " #divOptionalAssig");
                            optTag.html("This assignment is locked until you complete the required content above");

                            optTag.parent().css("color", "gray");
                        }

                    }

                }

                if ((hasPrereqs) && (moduleStates !== undefined))//don't block links if the professor logs in
                {
                    var links = $("#ulAssignments li a");
                    links.each(function (i) {
                        var parent = $(this).parent();//li
                        var cnt = $(this).contents();
                        $(this).replaceWith(cnt);
                        parent.css("cursor", "default");
                        parent.css("color", "rgb(162, 154, 158)");
                    });
                    $(".assignmentBox").css("background-color", "#F0F0F0");
                    $(".assignmentBox").hover(
                        function () {
                            $(this).css("border", "1px solid #D0D0D0");
                        });

                }

                var optDiv = $("#divOptionalAssignments");
                optionalTags ? optDiv.attr("class", "visible") : optDiv.attr("class", "hidden");
            }

        }

        return true;
    }


    function highlightPrerequisite(liSelect, prereqId)
    {
        var originalColors = [];
        var prereqDoms;

        liSelect
            .on("mouseenter", function (d)
            {
                prereqDoms = d3.select(".path" + prereqId)
                    .each(function(d){
                        originalColors[prereqId] = d.style("fill");
                        d.style("fill", "#FF6600");
                    });

                d3.select("#tooltip")
                    .attr("class", "seeThroughOnly");
            })
            .on("mouseleave", function (d)
            {
                d3.select(this).style("color", "black");
                d3.select("#tooltip")
                    .attr("class", "solid");
            });
    }


    function getTags(dd)
    {
        if(dd.published==="0")
        {
            return false;
        }
        if (dd.content.length < 1)
        {
            return null;
        }
        var icon = "";
        var selection = null;
        var optional = false;
        var markup = "";

//TODO: verify that this works
        var itemTags = dd.content[0].tags;
        itemTags = itemTags.toLowerCase();
        var tags = itemTags.split(", ");


        //priorities in tags are 1) Description, 2) Optional
        if ((tags.indexOf('description') > -1) && (dd.type === "SubHeader"))
        {
            $("#tooltipDesc").html(dd.title);//if the type is SubHeader we will assign it as Description and not add an li item for it
            return;
        }
        else if (tags.indexOf('optional') > -1)
        {
            icon = "icon-star";
            optional = true;
        }
        else
        {
            switch (dd.type) {
                case "File"://Reading Quiz
                    icon = "icon-file-word-o";
                    break;
                case "Page"://Writing Assignment
                    icon = "icon-pencil";
                    break;
                case "Discussion"://Game
                    icon = "icon-gamepad";//does not exist
                    break;
                case "Assignment"://Self assessment
                    icon = "icon-search-plus";
                    break;
                case "Quiz":
                    icon = "icon-book";
                    break;
                case "SubHeader":
                    icon = "icon-header";
                    break;
                case "ExternalUrl":
                    icon = "icon-book";
                    break;
                case "ExternalTool":
                    icon = "icon-book";
                    break;
                default:
                    icon = "icon-book";
            }
        }

        //get submission data
        var ob = accessibleSubmissData.filter(function (ob)
        {
            var id = dd.content_id;
            if (parseInt(ob.content_id) === parseInt(id))
            {
                return ob;
            }
        })[0];

        if (ob !== undefined)
        {
            markup = "";
            markup += "<li class='assignmentLi'>" +
                "<a href='" + dd.html_url + "' target='_blank'>" +
                "<div class='assignmentBox' id='content" + dd.content_id + "'>" +
                "<div class='divLeft'>" +
                "<span class='" + icon + "'></span><span id='spanAName'>" + dd.title + "</span>" +
                "</div>" +
                "<div class='divRight'>" +
                "<span id='spanAStatus' class='fa icon-check'></span>" +
                "<span id='divStatusDesc'>Complete</span>" +
                "<div class='divPoints'>Score:" + ob["score"] + "/" + dd.content[0].points_possible + "</div>" +
                "</div>" +
                "</div>" +
                "</a>" +
                "</li>";
        }
        else
        {
            if (dd.type === "SubHeader")
            {
                markup = "";
                markup += "<li class='assignmentLi subheaderLi'>" +
                    "<div class='subheaderBox' id='content" + dd.content_id + "'>" +
                    "<div class='divSubheader'>" +
                    "<h5>" + dd.title + "</h5>" +
                    "</div>" +
                    "</div>" +
                    "</li>";
            }
            else if ((dd.type === "Assigment") || (dd.type === "Quiz") || (dd.type === "Discussion"))
            {
                markup = "";
                markup += "<li class='assignmentLi'>" +
                    "<a href='" + dd.html_url + "' target='_blank'>" +
                    "<div class='assignmentBox' id='content" + dd.content_id + "'>" +
                    "<div class='divLeft'>" +
                    "<span class='" + icon + "'></span><span id='spanAName'>" + dd.title + "</span><div id='divOptionalAssig'></div>" +
                    "</div>" +
                    "<div class='divRight'>" +
                    "<div class='divPoints'>--/" + dd.content[0].points_possible + " pts</div>" +
                    "</div>" +
                    "</div>" +
                    "</a>" +
                    "</li>";
            }
            else
            {
                markup = "";
                markup += "<li class='assignmentLi'>" +
                    "<a href='" + dd.html_url + "' target='_blank'>" +
                    "<div class='assignmentBox' id='content" + dd.content_id + "'>" +
                    "<div class='divLeft'>" +
                    "<span class='" + icon + "'></span><span id='spanAName'>" + dd.title + "</span><div id='divOptionalAssig'></div>" +
                    "</div>" +
                    "</div>" +
                    "</a>" +
                    "</li>";
            }


        }



        //add the html markup to the appropriate ul
        optional ? selection = $("#ulOptionalAssignments") : selection = $("#ulAssignments");
        selection.append(markup);


        return optional;
    }
    /***************************************** tooltip to follow mouse on each movement ****************************************/
    function followCursor()
    {
        var tooltip = d3.select("#tooltip");
        //find width and height of tooltip and of the document.
        //if the natural x or y positions make it so the box goes outside of "margins" then choose a different x/y position
        var tipWidth = parseInt(tooltip.style("width"), 10);
        var tipHeight = parseInt(tooltip.style("height"), 10);
        var body = document.body,
            html = document.documentElement;

        var height = Math.max( body.scrollHeight, body.offsetHeight,
            html.clientHeight, html.scrollHeight, html.offsetHeight );
//        var visibleHeight = html.clientHeight;
//        var yOffset = window.pageYOffset;
        var docWidth = parseInt(window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth||0) - 30; ///-30 to account for the right scrolling bar
        var docHeight = parseInt(height);

        var x = parseInt((d3.mouse(body)[0]) + 20);
        var y = parseInt((d3.mouse(body)[1]) + 20);

        //when running into the right frame
        //only do this if the entire document width isn't smaller than the tooltip width
        if ((x + tipWidth) > docWidth)
        {//need to scroll left;
            x = x - ((x + tipWidth) - docWidth);
        }

        if (docHeight > tipHeight)
        {//when running into the bottom frame
            //only do this if the entire document height isn't smaller than the tooltip height
            var visibleHeight = html.clientHeight;
            var avaHeight = visibleHeight - y;
            if ((avaHeight) < (tipHeight / 2))
            {
                if (y - tipHeight > 0)
                {
                    y = y - tipHeight - 50;
                }
            }
        }

        $("#tooltip").offset({left: x, top: y});
        return true;
    }

    /***************************************** Highlight Current Tree ****************************************/
    function highlightCurrentTree(d)
    {
        var sequenceArray = getAncestors(d);
        //updateBreadcrumbs(sequenceArray, percentageString);

        // Fade all the segments.
        d3.selectAll("path")
            .style("opacity", 0.3);

        // Then highlight only those that are an ancestor of the current segment.
        svg.selectAll("path")
            .filter(function (node) {
                return (sequenceArray.indexOf(node) >= 0);
            })
            .style("opacity", 1);

        return true;
    }


    /***************************************** Get Ancestors ****************************************/
    function resetTreeOpacity()
    {
        // Deactivate all segments during transition.
        d3.selectAll("path").on("mouseover", null);

        d3.selectAll("path")
            .style("opacity", 1)
            .on("mouseover", highlightCurrentTree);

        return true;
    }

    /***************************************** Get Ancestors ****************************************/
// Given a node in a partition layout, return an array of all of its ancestor
// nodes, highest first,
    function getAncestors(node) {
        var path = [];
        var current = node;
        //we want to include the root too
        path.unshift(current);
        while (current.parent) {
            current = current.parent;
            path.unshift(current);
        }
        return path;
    }


    function breadcrumbPoints(d, i)
    {
        //b.w = (d.name.length*10);
        var points = [];
        points.push("0,0");
        points.push(b.w + ",0");
        points.push(b.w + b.t + "," + (b.h / 2));
        points.push(b.w + "," + b.h);
        points.push("0," + b.h);
        if (i > 0) { // Leftmost breadcrumb; don't include 6th vertex.
            points.push(b.t + "," + (b.h / 2));
        }
        return points.join(" ");
    }

    function breadCrumbFill(node)
    {
        var path = [];
        var current = node;
        while (current.parent) {
            path.unshift(current);
            current = current.parent;
        }
        var g = d3.select("#trail")
            .selectAll("g")
            .data(path, function (d) {
                return d.name + d.depth;
            });

        // Add breadcrumb and label for entering nodes.
        var entering = g.enter().append("svg:g");

        entering.append("svg:polygon")
            .attr("points", breadcrumbPoints)

            .style("fill", function (d) {
                if (newModuleStates !== undefined)
                {
                    var ob = newModuleStates.filter(function (ob)
                    {
                        var id = parseInt(d.module_id);
                        if (ob.module_id === id)
                        {
                            return ob;
                        }
                    })[0];

                    var newColor;
                    ob !== undefined ? newColor = ob.color : newColor = "#8F8F8F";
                    return newColor;

                }
                else
                {

                    return "#8F8F8F";
                }
            })
            .style("stroke", "white");

        //make the current polygon a different color
        //var lastPolygon = d3.select(d3.selectAll("polygon")[0].pop());
        //lastPolygon.style("fill","#fff")

        entering.append("svg:text")
            .attr("x", (b.w + b.t) / 2)
            .attr("y", b.h / 2)
            .attr("dy", "0.35em")
            .attr("text-anchor", "middle")
            .text(function (d) {

                var name = d.name;
                if (name.length > 22)
                {
                    name = name.substring(0, 19) + "...";
                }
                return name;
            });
        // Set position for entering and updating nodes.
        g.attr("transform", function (d, i) {
            return "translate(" + i * (b.w + b.s) + ", 0)";
        });

        // Remove exiting nodes.
        g.exit().remove();

        // Make the breadcrumb trail visible, if it's hidden.
        d3.select("#trail")
            .style("visibility", "")
            .style("overflow", "visible");
        return true;
    }

}

/*******************************Reset Tooltip Content*************************/
function resetTooltipContent()
{
    var assignments = d3.select("#ulAssignments");
    assignments.selectAll("*").remove();
    var optional = d3.select("#ulOptionalAssignments");
    optional.selectAll("*").remove();

    var prereqs = d3.select("#ulPrerequisites");
    prereqs.selectAll("*").remove();
    $("#tooltipDesc").html("");

    return true;
}

function processModuleStates(states,textStatus, jqXHR, stateColors)
{
    console.log(states);
    console.log("then new");
    for(var i=0;i<=rawData.length-1;i++)
    {
        var module = rawData[i];
        var thisModuleState="";
        var moduleColor="";
        var percentageCleared = 0.0;
        var items = module.module_items;
        var submittedItems =[];
        var path = d3.selectAll(".path" + module.module_id);
        //if there are subheaders among the module items we will remove them, because we don't want to take those into account when determining
        //the degree of completion for each module
        var subheaders = items.filter(function (obj)
        {
            if(obj.type=="SubHeader")
            {
                return obj;
            }
            else if(obj.type=="File")
            {
                return obj;
            }
        });
        if(subheaders.length>0)
        {
            for(var l=0;l<=subheaders.length-1;l++)
            {
                var index = items.indexOf(subheaders[l]);
                if (index > -1) {
                    items.splice(index, 1);
                }
            }
        }

        //if the module states say the module is locked then we'll display it as locked
        var submittedItems = states.filter(function (obj)
        {
            if(obj.module_id == module.module_id)
            {
                return obj;
            }
        })[0];

        if(submittedItems!=undefined && submittedItems.state =="locked")
        {
            thisModuleState = "locked";
            path.style("fill", stateColors["locked"]);
            moduleColor = stateColors["locked"];
            var moduleStateItem ={module_id:parseInt(module.module_id), color:moduleColor, state: thisModuleState};
            newModuleStates.push(moduleStateItem);
            continue;
        }

        //if there are no module items then the module is considered to be complete
        if(items.length<1)
        {
            percentageCleared = 100;

        }
        //if the item is not locked (meaning it's unlocked, in progres, or completed) we will analyze each module item in the module
        //if they're all completed we will make it the darkest blue. If it's in progress we'll make the shade of blue relative to the
        //degree of completion. If it is not locked but not started it will be green
        for(var k=0;k<=items.length-1;k++)
        {
            var currentItem = items[k];
            if(currentItem.completion_requirement=="")
            {
                var submission = getSubmissionForContent(currentItem.content_id);
                if(submission!=undefined)
                {
                    percentageCleared = percentageCleared+ (100/items.length);
                }
                continue;
            }
            var completion_requirement = JSON.parse(currentItem.completion_requirement);
            switch(completion_requirement.type)
            {
                case "must_submit":
                    var submission = getSubmissionForContent(currentItem.content_id);
                    if(submission!=undefined)
                    {
                        percentageCleared = percentageCleared+ (100/items.length);
                    }
                    break;
                case "min_score":
                    var minscore = completion_requirement.min_score;
                    var cleared = minScoreAchieved(minscore, currentItem.content_id);
                    if (cleared)
                    {
                        percentageCleared = percentageCleared+ (100/items.length);
                    }
                    break;
                case "must_contribute":
                    if(submittedItems!=undefined && submittedItems.state =="completed")
                    {//if canvas said the module was complete it must be because the item was contributed to
                        percentageCleared = percentageCleared+ (100/items.length);
                    }
                    break;
                case "must_view":
                    if(submittedItems!=undefined && submittedItems.state =="completed")
                    {//if canvas said the module was complete it must be because the item was viewed
                        percentageCleared = percentageCleared+ (100/items.length);
                    }
                    break;
            }
        }
        if(percentageCleared==0.0)
        {//unlocked and not started
            thisModuleState = "unlocked";
            path.style("fill", stateColors['unlocked']);
            moduleColor = stateColors['unlocked'];
        }
        else if(percentageCleared<100)
        {//in progress
            var newColor = LightenDarkenColor(stateColors["started"], -percentageCleared);
            thisModuleState = "in-progress";
            path.style("fill", newColor);
            moduleColor = newColor;
        }
        else
        {//completed
            thisModuleState = "completed";
            path.style("fill", stateColors["completed"]);
            moduleColor = stateColors['completed'];
        }

        var moduleStateItem ={module_id:parseInt(module.module_id), color:moduleColor, state: thisModuleState};
        newModuleStates.push(moduleStateItem);
    }
    console.log(newModuleStates);
}

function minScoreAchieved(minscore, content_id)
{

    var submittedItems = getSubmissionForContent(content_id);
    if(submittedItems!=undefined && submittedItems.score>=minscore)
    {
        return true;
    }
    else{return false;}
}

function getSubmissionForContent(content_id)
{
    var submittedItems = accessibleSubmissData.filter(function (obj)
    {
        if(obj.content_id == content_id)
        {
            return obj;
        }
    })[0];

    return submittedItems;
}
function getStudentSubmissions()
{
    if(submissionData==undefined)
    {
        // studentId = 1240358;
        var newPromise = $.get('getStudentSubmissions', {studentId: studentId, courseId: courseId});
        newPromise.then(function (data1) {
            showScores(data1);
            submissionData = data1;
            if(moduleStates!=undefined)
            {
                processModuleStates(moduleStates,null, null, stateColors);
            }
            else{
                getModuleStates();
            }

        }).fail(function (data2) {
        });
    }
    else
    {
        showScores(submissionData);
        if(moduleStates!=undefined)
        {
            processModuleStates(moduleStates,null, null, stateColors);
        }
        else{
            getModuleStates();
        }
    }




}

function getModuleStates()
{
    if(moduleStates===undefined)
    {
        var promise = $.get("getModuleStates");
        promise.then(function (data1, textStatus, jqXHR) {
                moduleStates = data1;
                processModuleStates(data1, textStatus, jqXHR, stateColors);
            })
            .fail(function (data2) {
            });
    }
    else
    {
        processModuleStates(moduleStates,null, null, stateColors);
    }
}
function showScores(data)
{//accessibleSubmissionData will be submission_type, content_id, grade
    var rawSubmissionData = data;
    var content_id;
    for (var i = 0; i <= data.length - 1; i++)
    {
        d = data[i];
        if(d.submitted_at===null)
        {
            continue;
        }
        var item = new Object();
        item['submission_type'] = d["submission_type"];
        //check that the assignment is a quiz
        if (d["submission_type"] === "online_quiz")
        {

            item["grade"] = d["grade"];

            item["score"] = d["score"];
            //the "body" object of the submission has a quiz id that matches the content_id [only for quizzes!]
            var s = d["body"];
            var q = s.split(",");
            for (var j = 0; j <= q.length - 1; j++)
            {
                //if the string contains "Quiz"
                if (q[j].indexOf("quiz") > -1)
                {
                    var r = q[j].replace(/\s+/g, '');//remove all white spaces before exploding the string
                    var quizId = r.split(":")[1];
                    content_id = quizId;
                    item["content_id"] = content_id;
                    break;
                }
            }
        }
        accessibleSubmissData.push(item);
    }
}

function hideModalPopup(content)
{
    d3.select("#tooltip").attr("class", "hidden");
    d3.select("#blocker").attr("class", "hidden");

    //remove assignments and pre-reqs from modal popup
    resetTooltipContent();
    return true;
}
function modalBoxShow(content) {
    //IF THE BOX WAS SORT OF HIDDEN BEFORE, HERE WE NEED TO SLIDE IT UP SO ALL THE CONTENT IS VISIBLE.
    var animateY = false;
    var animateX = false;

    var scrollTop = $(window).scrollTop();
    var scrollLeft = $(window).scrollLeft();

    var docWidth = parseInt(window.innerWidth);
    var docHeight = parseInt(window.innerHeight);

    var tooltip = $("#tooltip");
    var tipWidth = parseInt(tooltip.width(), 10);
    var tipHeight = parseInt(tooltip.height(), 10);

    //Get x/y values for the tooltip
//     var x = tooltip.offset().left;
    var x = tooltip.offset().left-scrollLeft;
//     var y = tooltip.offset().top;
    var y = tooltip.offset().top-scrollTop;

    var avaWidth = docWidth - x;
    if (avaWidth < tipWidth)
    {//slide it left by
        animateX = true;
        x = tipWidth - avaWidth;

    }

    var avaHeight = docHeight - y;
    if (avaHeight < tipHeight)
    {
        animateY = true;
        y = scrollTop + y-(tipHeight-avaHeight)-20;//give it a little extra room when it slides up
    }

    if (animateY)
    {
        $("#tooltip").animate({
            top: y
        }, 300);
    }

    if (animateX)
    {
        $("#tooltip").animate({
            left: x
        }, 300);
    }

    d3.select("#divInnerTooltip")
        .attr("class", "showOverflow");


    //add click event to close button of modal popup

    var btn = d3.select(".imgClose");
    btn.on("click", function () {
        //hide the popup
        hideModalPopup();
    });


    d3.select("#tooltip")
        .attr("class", "solid");

    //blocker
    d3.select("#blocker").attr("class", "show")
        .on("click", function () {
            hideModalPopup();
        });

    //reset scroll
    var div = document.getElementById("divInnerTooltip");
    div.scrollTop = 0;
    div.scrollLeft = 0;
    return true;
}//function


function LightenDarkenColor(col, amt) {

    var usePound = false;

    if (col[0] == "#") {
        col = col.slice(1);
        usePound = true;
    }
    var num = parseInt(col,16);
    var r = (num >> 16) + amt;
    if (r > 255) r = 255;
    else if  (r < 0) r = 0;

    var b = ((num >> 8) & 0x00FF) + amt;
    if (b > 255) b = 255;
    else if  (b < 0) b = 0;

    var g = (num & 0x0000FF) + amt;
    if (g > 255) g = 255;
    else if (g < 0) g = 0;

    var newColor =  (usePound?"#":"") + String("000000" + (g | (b << 8) | (r << 16)).toString(16)).slice(-6);
    return newColor;

}