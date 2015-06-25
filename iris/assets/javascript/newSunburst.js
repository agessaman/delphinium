//GLOBAL VARIABLES
var rawSubmissionData;
var accessibleSubmissData = new Array();
var moduleStates;

//TODO: make colors configurable
var stateColors = {locked: "#8F8F8F", unlocked: "#588238", started: "#3087B4", completed: "#143D55"};
var backColors = {locked: "#DDDDDD", unlocked: "#588238", started: "#3087B4", completed: "#133D55"};


$(document).ready(function () {
    graphData = graphData[0];
    console.log(graphData);

    //set up environment
    var width = 960,
            height = 700,
            radius = Math.min(width, height) / 2;

    /*********************************Make sunburst chart****************************************/

    var svg = d3.select("#wrapper").append("svg")
            .attr("id", "svg")
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
//                .on("click", function(d){
//                    modalBoxShow(d);
//                })
            .style("stroke", "#fff")
            .style("fill", function (d) {
                return "#8F8F8F";
            })
            .style("fill-rule", "evenodd")
            .style("cursor", "pointer");

    d3.select("#stackImgClose")
            .on("mouseenter", function (d) {
                d3.select("#circle").classed("icon-circle-thin fa-stack-2x", true);
            })
            .on('mouseleave', function (d) {
                d3.select("#circle").classed("icon-circle-thin fa-stack-2x", false);
            });



    /*********************************Show Tooltip********************************/
    /****5/27/2014****/
    function showTooltip(d)
    {
        resetTooltipContent();
        var hasPrereqs = false;
        //make the title bar the same color to represent the state
        var backColor = backColors['locked'];

        if (moduleStates !== undefined)
        {
            var ob = moduleStates.filter(function (ob)
            {
                var id = parseInt(d.module_id);
                if (ob.module_id === id)
                {
                    return ob;
                }
            })[0];

            if (ob !== undefined) {
                var newColor = stateColors[ob.state];
                var backColor = backColors[ob.state];
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
            var prereqs = d.prerequisite_module_ids.split(",");
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
                        return obj["state"] !== 'completed';
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
                                if (obj.module_id === prereqs)
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

                //Highlight prerequisite
                var originalColor = "";


                ulPrereqs
                        .on("mouseenter", function (prereqs)
                        {
                            var mod = d3.select("#path" + prereqs);
                            originalColor = mod.style("fill");
                            mod.style("fill", "#FF6600");

                            d3.select("#tooltip")
                                    .attr("class", "seeThroughOnly");
                        })
                        .on("mouseleave", function (prereqs)
                        {
                            d3.select(this).style("color", "black");
                            d3.select("#tooltip")
                                    .attr("class", "solid");

                            var mod = d3.select("#path" + prereqs)
                                    .style("fill", originalColor);
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

//        $(".assignmentBox").css("background-color",backColor);
            if (optionalTags)
            {
                var ob = [];
                //only display optional tags if required content has been completed
                for (var i = 0; i <= requiredAssignments.length - 1; i++)
                {
                    //only display optional tags if required content has been completed
                    ob = accessibleSubmissData.filter(function (ob)
                    {
                        if (ob.content_id === requiredAssignments[i.content_id])
                        {
                            return ob;
                        }
                    });
                }

                if (ob.length !== requiredAssignments.length)
                {
                    for (var j = 0; j <= optTagsArr.length - 1; j++)
                    {
                        var parent = $("#content" + optTagsArr[j].content_id).parent();
                        var cnt = parent.contents();
                        parent.replaceWith(cnt);
                        var optTag = $("#content" + optTagsArr[j].content_id + " #divOptionalAssig");
                        optTag.html("You must finished the required assignments first");

                        optTag.parent().css("color", "gray");
                    }

                }

            }

            if (hasPrereqs)
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

        return true;
    }

    function highlightPrerequisite(liSelect, prereqId)
    {
        var originalColor = "";


        liSelect
                .on("mouseenter", function (d)
                {
                    var mod = d3.select("#path" + prereqId);
                    originalColor = mod.style("fill");
                    mod.style("fill", "#FF6600");

                    d3.select("#tooltip")
                            .attr("class", "seeThroughOnly");
                })
                .on("mouseleave", function (d)
                {
                    d3.select(this).style("color", "black");
                    d3.select("#tooltip")
                            .attr("class", "solid");

                    var mod = d3.select("#" + prereqId)
                            .style("fill", originalColor);
                });
    }


    function getTags(dd)
    {
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
            if (ob.content_id === id)
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
                        "<a href='" + dd.html_url + "' target='_blank'>" +
                        "<div class='subheaderBox' id='content" + dd.content_id + "'>" +
                        "<div class='divSubheader'>" +
                        "<h5>" + dd.title + "</h5>" +
                        "</div>" +
                        "</div>" +
                        "</a>" +
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

    function followCursor() {
        var tooltip = d3.select("#tooltip");
        //find width and height of tooltip and of the document.
        //if the natural x or y positions make it so the box goes outside of "margins" then choose a different x/y positions
        var tipWidth = parseInt(tooltip.style("width"), 10);
        var tipHeight = parseInt(tooltip.style("height"), 10);

//    var docWidth = parseInt(window.innerWidth);
//    var docHeight = parseInt(window.innerHeight);
        var docWidth = parseInt(window.innerWidth) - 30; ///-30 to account for the right scrolling bar
        var docHeight = parseInt(window.innerHeight);

        //Get x/y values for the tooltip
        var wrap = d3.select("#wrapper").node();
        var x = parseInt((d3.mouse(wrap)[0]) + 20);
        var y = parseInt((d3.mouse(wrap)[1]) + 60);

        //when running into the right frame
        //only do this if the entire document width isn't smaller than the tooltip width

        if ((x + tipWidth) > docWidth)
        {
            //need to scroll left;
            x = x - ((x + tipWidth) - docWidth);
        }

        //when running into the bottom frame
        //only do this if the entire document height isn't smaller than the tooltip height
        if (docHeight > tipHeight)
        {
            var avaHeight = docHeight - y;
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
    /***************************************** Make breadcrumb ****************************************/
    var breadCrumb = [['width'], ['points'], ['name'], ['path']];

    var b = {
        w: 94, h: 15, s: 2, t: 10
    };

    function breadcrumbPoints(d, i) {
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
    function breadCrumbFill(node) {
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
                    if (moduleStates !== undefined)
                    {
                        var ob = moduleStates.filter(function (ob)
                        {
                            var id = parseInt(d.module_id);
                            if (ob.moduleId === id)
                            {
                                return ob;
                            }
                        })[0];

                        var newColor;
                        ob !== undefined ? newColor = stateColors[ob.state] : newColor = "#8F8F8F";
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

        //var lastText = d3.select(d3.selectAll("text")[0].pop());
        //lastText.style("fill","#2C2F34");

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

    //CHECK THE STATE OF THE MODULES FOR THE GIVEN USER
    if (studentId === undefined)
    {
        enableClickEvent();
    }
    else
    {
        var promise = $.get("getModuleStates");
        promise.then(function(data1,textStatus, jqXHR){
            console.log("trying to get module states");
                 getModuleStates(data1, textStatus, jqXHR, stateColors);
        })
        .fail(function(data2){
//            console.log("failed getModuleStates"+JSON.stringify(data2));
        });
    }
    
});

//catch escape event for when modal window is showing
$(document).keyup(function (e) {   // enter   
    if (e.keyCode == 27) {
        if ($("#blocker").hasClass("show"))
        {
            hideModalPopup();
        }
    }
});

function enableClickEvent()
{
    console.log("enabling click event");
    var path = d3.select("#wrapper g").selectAll("path")
    .on("click", function (d) {
        console.log("attached click event");
        modalBoxShow(d);
    });
}
function checkModuleCompletion()
{
    accessibleSubmissData;
    for (var i = 0; i <= graphData.length - 1; i++)
    {

    }
}

function getModuleStates(data1, textStatus, jqXHR, stateColors)
{
    tempStates = data1;
    tempModData = graphData.children;
    for (var i = 0; i <= data1.length - 1; i++)
    {
        d = data1[i];
        var mod = d3.select("#path" + d.module_id);
        if (mod[0][0] !== null)
        {
            var newColor = stateColors[d.state];
            mod.style("fill", newColor);
        }
    }
    moduleStates = tempStates;

    var newPromise = $.get('getStudentSubmissions', { studentId: studentId, courseId : courseId});
    newPromise.then(function(data1){
        console.log("got submission");
        showScores(data1);
        enableClickEvent();
    }).fail(function(data2){
        console.log("failed getStudentSubmissions");
    });
}
function showScores(data)
{
    //accessibleSubmissionData will be submission_type, content_id, grade
    var rawSubmissionData = data;
    var content_id;
    for (var i = 0; i <= data.length - 1; i++)
    {
        d = data[i];
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
/*******************************Close Modal Popup*************************/
function hideModalPopup(content)
{
    d3.select("#tooltip").attr("class", "hidden");
    d3.select("#blocker").attr("class", "hidden");

    //remove assignments and pre-reqs from modal popup
    resetTooltipContent();
    return true;
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

/*****************Show Modal Window********************************/
/****5/14/2014****/

//display modal Window
function modalBoxShow(content) {
    //IF THE BOX WAS SORT OF HIDDEN BEFORE, HERE WE NEED TO SLIDE IT UP SO ALL THE CONTENT IS VISIBLE.
    var slideTooltip = false;


    var docWidth = parseInt(window.innerWidth);
    var docHeight = parseInt(window.innerHeight);

    var tooltip = $("#tooltip");
    var tipWidth = parseInt(tooltip.width(), 10);
    var tipHeight = parseInt(tooltip.height(), 10);

    //Get x/y values for the tooltip
    var x = tooltip.offset().left;
    var y = tooltip.offset().top;

    var avaWidth = docWidth - x;
    if (avaWidth < tipWidth)
    {//slide it left by
        slideTooltip = true;
        x = tipWidth - avaWidth;

    }

    var avaHeight = docHeight - y;
    if (avaHeight < tipHeight)
    {
        slideTooltip = true;
        y = tipHeight - avaHeight;
    }

    if (slideTooltip)
    {
        $("#tooltip").animate({
            left: x,
            top: y
        }, 500);
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