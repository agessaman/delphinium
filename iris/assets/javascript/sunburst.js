$(document).ready(function() {
	
            //and another way
    
    /*
            var graphData2;
            d3.json("orgBehavior.js", function(error, json) {
                if (error) 
                {
                    return console.warn(error);
                }
                graphData2 = json;
            });
    */
    
    //From php we should generate a file like this one below, where we can use the id of 
    //the assignments and obtain the assignment name, the module number and the module name. 
    //Or alternatively, we could include that in the json file.. 
    //an object with the following format:
    //assignmentId: ["Assignment Name", "module id", "Module Name"]
    
    
    //ORIGINAL GRAPHDATA
    
    var graphData = 
        {
  "id": "m1",
  "name": "Organizational Behavior",
  "description": "Organizational Behavior Description",
  "parent": "",
  "percentageCompleted": ".85",
  "pointsAchieved": "100",
  "pointsPossible": "140",
  "color": "#A7A53B",
  "prereqs": [],
  "locked": "0",
  "assignments": [
    {
      "id": "a1",
      "name": "Assignment 1",
      "description": "Lorem ipsum dolor sit amet.",
      "score": "95.4",
      "type": "1",
      "status": "1",
      "pointsPossible": "200",
      "pointsAchieved": "180",
      "url": "http://google.com",
      "optional": "0",
      "prereqs": []
    }
  ],
  "children": [
    {
      "id": "m2",
      "name": "Introduction",
      "description": "Lorem ipsum dolor sit amet.",
      "parent": "m1",
      "percentageCompleted": ".8",
      "pointsAchieved": "100",
      "pointsPossible": "140",
      "color": "#0066A8",
      "prereqs": [],
      "locked": "0",
      "assignments": [
        {
          "id": "a2",
          "name": "Assignment 2",
          "description": "Lorem ipsum dolor sit amet.",
          "score": "95.4",
          "type": "2",
          "status": "1",
          "pointsPossible": "200",
          "pointsAchieved": "180",
          "url": "http://gmail.com",
          "optional": "0",
          "prereqs": []
        }
      ],
      "children": [
        {
          "id": "m8",
          "name": "Getting Started",
          "description": "Lorem ipsum dolor sit amet.",
          "parent": "m2",
          "percentageCompleted": ".8",
          "pointsAchieved": "100",
          "pointsPossible": "140",
          "color": "#143D55",
          "prereqs": [
            {
              "mId": "m1",
              "mName": "Organizational Behavior",
              "aId": "a1",
              "aName": "Assignment 1",
              "url": "http://google.com"
            },
            {
              "mId": "m2",
              "mName": "Introduction",
              "aId": "a2",
              "aName": "Assignment 2",
              "url": "http://google.com"
            }
          ],
          "locked": "0",
          "assignments": [
            {
              "id": "a9",
              "name": "Assignment 9",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "3",
              "status": "2",
              "pointsPossible": "200",
              "pointsAchieved": "0",
              "url": "http://google.com",
              "optional": "0",
              "prereqs": []
            },
            {
              "id": "a10",
              "name": "Assignment 10",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "4",
              "status": "3",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://google.com",
              "optional": "0",
              "prereqs": [
                {
                  "id": "a9",
                  "name": "Assignment 9"
                }
              ]
            }
          ]
        },
        {
          "id": "m9",
          "name": "What is an organization?",
          "description": "Lorem ipsum dolor sit amet.",
          "parent": "m2",
          "percentageCompleted": ".8",
          "pointsAchieved": "100",
          "pointsPossible": "140",
          "color": "#3087B4",
          "prereqs": [],
          "locked": "0",
          "assignments": [
            {
              "id": "a11",
              "name": "Assignment 11",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "1",
              "status": "4",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://google.com",
              "optional": "0",
              "prereqs": []
            },
            {
              "id": "a12",
              "name": "Assignment 12",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "2",
              "status": "5",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://google.com",
              "optional": "1",
              "prereqs": [
                {
                  "id": "a9",
                  "name": "Assignment 9"
                },
                {
                  "id": "a7",
                  "name": "Assignment 7"
                },
                {
                  "id": "a4",
                  "name": "Assignment 4"
                }
              ]
            }
          ]
        },
        {
          "id": "m10",
          "name": "What is organizational behavior?",
          "description": "Lorem ipsum dolor sit amet.",
          "parent": "m2",
          "percentageCompleted": ".8",
          "pointsAchieved": "100",
          "pointsPossible": "140",
          "color": "#A7A53B",
          "prereqs": [],
          "locked": "1",
          "assignments": [
            {
              "id": "a13",
              "name": "Assignment 13",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "2",
              "status": "1",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://gmail.com",
              "optional": "0",
              "prereqs": []
            }
          ],
          "children": [
            {
              "id": "m13",
              "name": "Why does it matter?",
              "description": "Lorem ipsum dolor sit amet.",
              "parent": "m10",
              "percentageCompleted": ".8",
              "pointsAchieved": "100",
              "pointsPossible": "140",
              "color": "#A7A53B",
              "prereqs": [],
              "locked": "1",
              "assignments": [
                {
                  "id": "a14",
                  "name": "Assignment 14",
                  "description": "Lorem ipsum dolor sit amet.",
                  "score": "95.4",
                  "type": "3",
                  "status": "2",
                  "pointsPossible": "200",
                  "pointsAchieved": "180",
                  "url": "http://google.com",
                  "optional": "0",
                  "prereqs": []
                }
              ]
            },
            {
              "id": "m14",
              "name": "Trends and changes",
              "description": "Lorem ipsum dolor sit amet.",
              "parent": "m10",
              "percentageCompleted": ".8",
              "pointsAchieved": "100",
              "pointsPossible": "140",
              "color": "#8F8F8F",
              "prereqs": [],
              "locked": "0",
              "assignments": [
                {
                  "id": "a15",
                  "name": "Assignment 15",
                  "description": "Lorem ipsum dolor sit amet.",
                  "score": "95.4",
                  "type": "3",
                  "status": "3",
                  "pointsPossible": "200",
                  "pointsAchieved": "180",
                  "url": "http://google.com",
                  "optional": "0",
                  "prereqs": []
                }
              ]
            }
          ]
        },
        {
          "id": "m11",
          "name": "How are organizations studied?",
          "description": "Lorem ipsum dolor sit amet.",
          "parent": "m2",
          "percentageCompleted": ".8",
          "pointsAchieved": "100",
          "pointsPossible": "140",
          "color": "#999999",
          "prereqs": [],
          "locked": "0",
          "assignments": [
            {
              "id": "a12",
              "name": "Assignment 12",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "4",
              "status": "4",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://google.com",
              "optional": "0",
              "prereqs": []
            }
          ],
          "children": [
            {
              "id": "m17",
              "name": "Theory",
              "description": "Lorem ipsum dolor sit amet.",
              "parent": "m11",
              "percentageCompleted": ".8",
              "pointsAchieved": "100",
              "pointsPossible": "140",
              "color": "#A7A53B",
              "prereqs": [],
              "locked": "1",
              "assignments": [
                {
                  "id": "a18",
                  "name": "Assignment 18",
                  "description": "Lorem ipsum dolor sit amet.",
                  "score": "95.4",
                  "type": "4",
                  "status": "5",
                  "pointsPossible": "200",
                  "pointsAchieved": "180",
                  "url": "http://google.com",
                  "optional": "0",
                  "prereqs": []
                }
              ],
              "children": [
                {
                  "id": "m18",
                  "name": "Social Psychology",
                  "description": "Lorem ipsum dolor sit amet.",
                  "parent": "m17",
                  "percentageCompleted": ".8",
                  "pointsAchieved": "100",
                  "pointsPossible": "140",
                  "color": "#A7A53B",
                  "prereqs": [],
                  "locked": "1",
                  "assignments": [
                    {
                      "id": "a19",
                      "name": "Assignment 19",
                      "description": "Lorem ipsum dolor sit amet.",
                      "score": "95.4",
                      "type": "1",
                      "status": "1",
                      "pointsPossible": "200",
                      "pointsAchieved": "180",
                      "url": "http://google.com",
                      "optional": "0",
                      "prereqs": []
                    }
                  ]
                }
              ]
            }
          ]
        },
        {
          "id": "m12",
          "name": "History of Management",
          "description": "Lorem ipsum dolor sit amet.",
          "parent": "m2",
          "percentageCompleted": ".8",
          "pointsAchieved": "100",
          "pointsPossible": "140",
          "color": "#999999",
          "prereqs": [],
          "locked": "0",
          "assignments": [
            {
              "id": "a13",
              "name": "Assignment 13",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "2",
              "status": "2",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://google.com",
              "optional": "0",
              "prereqs": []
            }
          ]
        }
      ]
    },
    {
      "id": "m3",
      "name": "Getting the right people",
      "description": "Lorem ipsum dolor sit amet.",
      "parent": "m1",
      "percentageCompleted": ".8",
      "pointsAchieved": "100",
      "pointsPossible": "140",
      "color": "#999999",
      "prereqs": [],
      "locked": "0",
      "assignments": [
        {
          "id": "a3",
          "name": "Assignment 3",
          "description": "Lorem ipsum dolor sit amet.",
          "score": "95.4",
          "type": "5",
          "status": "5",
          "pointsPossible": "200",
          "pointsAchieved": "180",
          "url": "http://google.com",
          "optional": "0",
          "prereqs": []
        }
      ]
    },
    {
      "id": "m4",
      "name": "Aligning people to the organizational ideas",
      "description": "Lorem ipsum dolor sit amet.",
      "parent": "m1",
      "percentageCompleted": ".8",
      "pointsAchieved": "100",
      "pointsPossible": "140",
      "color": "#8F8F8F",
      "prereqs": [],
      "locked": "1",
      "assignments": [
        {
          "id": "a5",
          "name": "Assignment 5",
          "description": "Lorem ipsum dolor sit amet.",
          "score": "95.4",
          "type": "4",
          "status": "4",
          "pointsPossible": "200",
          "pointsAchieved": "180",
          "url": "http://google.com",
          "optional": "0",
          "prereqs": []
        }
      ]
    },
    {
      "id": "m5",
      "name": "Individual psychology in organizations",
      "description": "Lorem ipsum dolor sit amet.",
      "parent": "m1",
      "percentageCompleted": ".8",
      "pointsAchieved": "100",
      "pointsPossible": "140",
      "color": "#8F8F8F",
      "prereqs": [],
      "locked": "1",
      "assignments": [
        {
          "id": "a6",
          "name": "Assignment 6",
          "description": "Lorem ipsum dolor sit amet.",
          "score": "95.4",
          "type": "1",
          "status": "5",
          "pointsPossible": "200",
          "pointsAchieved": "180",
          "url": "http://google.com",
          "optional": "0",
          "prereqs": []
        }
      ],
      "children": [
        {
          "id": "m15",
          "name": "Attitudes",
          "description": "Lorem ipsum dolor sit amet.",
          "parent": "m5",
          "percentageCompleted": ".8",
          "pointsAchieved": "100",
          "pointsPossible": "140",
          "color": "#8F8F8F",
          "prereqs": [],
          "locked": "0",
          "assignments": [
            {
              "id": "a16",
              "name": "Assignment 16",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "2",
              "status": "1",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://google.com",
              "optional": "0",
              "prereqs": []
            }
          ],
          "children": [
            {
              "id": "m16",
              "name": "Impact on behavior",
              "description": "Lorem ipsum dolor sit amet.",
              "parent": "m15",
              "percentageCompleted": ".8",
              "pointsAchieved": "100",
              "pointsPossible": "140",
              "color": "#8F8F8F",
              "prereqs": [],
              "locked": "0",
              "assignments": [
                {
                  "id": "a17",
                  "name": "Assignment 17",
                  "description": "Lorem ipsum dolor sit amet.",
                  "score": "95.4",
                  "type": "3",
                  "status": "2",
                  "pointsPossible": "200",
                  "pointsAchieved": "180",
                  "url": "http://google.com",
                  "optional": "0",
                  "prereqs": []
                }
              ],
              "children": []
            }
          ]
        }
      ]
    },
    {
      "id": "m6",
      "name": "Social processes in organizations",
      "description": "Lorem ipsum dolor sit amet.",
      "parent": "m1",
      "percentageCompleted": ".8",
      "pointsAchieved": "100",
      "pointsPossible": "140",
      "color": "#8F8F8F",
      "prereqs": [],
      "locked": "1",
      "assignments": [
        {
          "id": "a7",
          "name": "Assignment 7",
          "description": "Lorem ipsum dolor sit amet.",
          "score": "95.4",
          "type": "5",
          "status": "3",
          "pointsPossible": "200",
          "pointsAchieved": "180",
          "url": "http://google.com",
          "optional": "0",
          "prereqs": []
        }
      ],
      "children": [
        {
          "id": "m19",
          "name": "Teams",
          "description": "Lorem ipsum dolor sit amet.",
          "parent": "m6",
          "percentageCompleted": ".8",
          "pointsAchieved": "100",
          "pointsPossible": "140",
          "color": "#8F8F8F",
          "prereqs": [],
          "locked": "1",
          "assignments": [
            {
              "id": "a20",
              "name": "Assignment 20",
              "description": "Lorem ipsum dolor sit amet.",
              "score": "95.4",
              "type": "1",
              "status": "4",
              "pointsPossible": "200",
              "pointsAchieved": "180",
              "url": "http://google.com",
              "optional": "0",
              "prereqs": []
            }
          ],
          "children": [
            {
              "id": "m20",
              "name": "Roles",
              "description": "Lorem ipsum dolor sit amet.",
              "parent": "m19",
              "percentageCompleted": ".8",
              "pointsAchieved": "100",
              "pointsPossible": "140",
              "color": "#8F8F8F",
              "prereqs": [],
              "locked": "1",
              "assignments": [
                {
                  "id": "a21",
                  "name": "Assignment 21",
                  "description": "Lorem ipsum dolor sit amet.",
                  "score": "95.4",
                  "type": "2",
                  "status": "5",
                  "pointsPossible": "200",
                  "pointsAchieved": "180",
                  "url": "http://google.com",
                  "optional": "0",
                  "prereqs": []
                }
              ],
              "children": [
                {
                  "id": "m21",
                  "name": "Types",
                  "description": "Lorem ipsum dolor sit amet.",
                  "parent": "m20",
                  "percentageCompleted": ".8",
                  "pointsAchieved": "100",
                  "pointsPossible": "140",
                  "color": "#8F8F8F",
                  "prereqs": [],
                  "locked": "1",
                  "assignments": [
                    {
                      "id": "a22",
                      "name": "Assignment 22",
                      "description": "Lorem ipsum dolor sit amet.",
                      "score": "95.4",
                      "type": "2",
                      "status": "5",
                      "pointsPossible": "200",
                      "pointsAchieved": "180",
                      "url": "http://google.com",
                      "optional": "0",
                      "prereqs": []
                    }
                  ],
                  "children": [
                    {
                      "id": "m22",
                      "name": "Top Management",
                      "description": "Lorem ipsum dolor sit amet.",
                      "parent": "m21",
                      "percentageCompleted": ".8",
                      "pointsAchieved": "100",
                      "pointsPossible": "140",
                      "color": "#8F8F8F",
                      "prereqs": [],
                      "locked": "1",
                      "assignments": [
                        {
                          "id": "a23",
                          "name": "Assignment 23",
                          "description": "Lorem ipsum dolor sit amet.",
                          "score": "95.4",
                          "type": "3",
                          "status": "1",
                          "pointsPossible": "200",
                          "pointsAchieved": "180",
                          "url": "http://google.com",
                          "optional": "0",
                          "prereqs": []
                        }
                      ]
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "id": "m7",
      "name": "Organizations are ideas in people",
      "description": "Lorem ipsum dolor sit amet.",
      "parent": "m1",
      "percentageCompleted": ".8",
      "pointsAchieved": "100",
      "pointsPossible": "140",
      "color": "#8F8F8F",
      "prereqs": [],
      "locked": "1",
      "assignments": [
        {
          "id": "a8",
          "name": "Assignment 8",
          "description": "Lorem ipsum dolor sit amet.",
          "score": "95.4",
          "type": "4",
          "status": "2",
          "pointsPossible": "200",
          "pointsAchieved": "180",
          "url": "http://google.com",
          "optional": "0",
          "prereqs": []
        }
      ]
    }
  ]
};
			//set up environment
			var width = 960,
			height = 700,
			radius = Math.min(width, height) / 2;
			
/*********************************Make sunburst chart****************************************/
                
			var svg = d3.select("#wrapper").append("svg")
				.attr("width", width)
				.attr("height", height)
				.append("g")
				.attr("transform", "translate(" + width / 2 + "," + height * .5 + ")");

			var partition = d3.layout.partition()
				.sort(null)
				.size([2 * Math.PI, radius])
				.value(function(d) { return 1; });

			var arc = d3.svg.arc()
				.startAngle(function(d) { return d.x; })
				.endAngle(function(d) { return d.x + d.dx; })
				.innerRadius(function(d) { return d.y; })
				.outerRadius(function(d) { return d.y + d.dy;});
    
			var path = svg.datum(graphData).selectAll("path")
				.data(partition.nodes)
				.enter().append("path")
                .attr("id", function(d) { return d.id; })
				.attr("d", arc)
                .on("mouseenter", function(d){
                    showTooltip(d);
                })
                .on("mousemove", function(d){
                    followCursor();
                    highlightCurrentTree(d);
                })
                .on('mouseleave',function(d){
                    resetTreeOpacity();
                    if(!$("#blocker").hasClass("show"))
                    {
                        d3.select("#tooltip").attr("class","hidden");
                    }
				}) 
                .on("click", function(d){
                    modalBoxShow(d);
                })
				.style("stroke", "#fff")
				.style("fill", function(d) { return d.color; })
				.style("fill-rule", "evenodd")
                .style("cursor","pointer");
			
 
/*****************Show Modal Window********************************/      
/****5/14/2014****/
            
//display modal Window
function modalBoxShow(content){
    //IF THE BOX WAS SORT OF HIDDEN BEFORE, HERE WE NEED TO SLIDE IT UP SO ALL THE CONTENT IS VISIBLE.
    var slideTooltip = false;
    
    var docWidth = parseInt(document.documentElement.clientWidth);
    var docHeight = parseInt(document.documentElement.clientHeight);

    var tooltip = $("#tooltip");
    var tipWidth = parseInt(tooltip.width(), 10);
    var tipHeight = parseInt(tooltip.height(),10);

    //Get x/y values for the tooltip
    var x = tooltip.offset().left;
    var y = tooltip.offset().top;

    var avaWidth = docWidth-x;
    if(avaWidth<tipWidth)
    {//slide it left by
        slideTooltip = true;
        x = tipWidth - avaWidth;
        
    }
    
    var avaHeight = docHeight-y;
    if(avaHeight<tipHeight)
    {
        slideTooltip = true;
        y = tipHeight-avaHeight;
    }

    if(slideTooltip)
    {
         $( "#tooltip" ).animate({
            left:x,
            top:y
            }, 500);
    }
    
    
    //clean up stuff from the transparent window
    var temp = d3.select("#ulAssignments").selectAll("li");
    temp.remove();
    temp=d3.select("#ulPrerequisites").selectAll("li");
    temp.remove();
    
    //ASSIGNMENTS 
    var $selection  =null;
    var assignmentsArray = content.assignments;
    var length = assignmentsArray.length
    for(var i=0;i<=length-1;i++)
    {
        var assignmentType,typeClass,typeStr, statusClass, statusStr, pointsStr, prereqs;
        
        switch(assignmentsArray[i].type)
                {
                    case "1":
                        typeClass = "fa fa-book";
                        typeStr = "Reading Quiz";
                        break;
                    case "2":
                        typeClass = "fa fa-pencil-square-o";
                        typeStr = "Writing Assignment";
                        break;
                    case "3":
                        typeClass = "fa fa-gamepad";
                        typeStr = "Game";
                        break;
                    case "4":
                        typeClass = "fa fa-search";
                        typeStr = "Self Assessment";
                        break;
                    case "5":
                        typeClass = "fa fa-book";
                        typeStr = "Reading";
                        break;
                    default:
                        typeClass = "fa fa-list";
                        typeStr = "Other";
                }
        
        switch(assignmentsArray[i].status)
        {
                case "1":
                    statusClass= "fa fa-check-square-o";
                    statusStr = "Done";
                    break;
                case "2":
                    statusClass= "fa fa-unlock";
                    statusStr = "Unlocked";
                    break;
                case "3":
                    statusClass= "fa fa-lock";
                    statusStr = "Locked";
                    break;
                case "4":
                    statusClass= "fa fa-spinner fa-spin";
                    statusStr = "In progress";
                    break;
                case "5":
                    statusClass= "fa fa-gift";
                    statusStr = "Optional";
                    break;
                default:
                    statusClass= "fa fa-pencil";
                    statusStr = "Other";
        }
        
    if(assignmentsArray[i].pointsAchieved==0)
        {
            pointsStr = "Pts: --/" + assignmentsArray[i].pointsPossible;
        }
        else
        {
            pointsStr = "Pts: " + assignmentsArray[i].pointsAchieved + "/" + assignmentsArray[i].pointsPossible;
        }
       
        if(assignmentsArray[i].prereqs.length >0)
        {
            prereqs = ""
            if(assignmentsArray[i].prereqs.length > 1)
            {
                prereqs = "Prerequisites: ";
                for(var j = 0;j<=assignmentsArray[i].prereqs.length-1;j++)
                {
                    prereqs += assignmentsArray[i].prereqs[j].name;
                    if(j < assignmentsArray[i].prereqs.length-1)
                    {
                      prereqs+= ", ";  
                    }
                }
            }
            else
            {
                prereqs = "Prerequisite: " + assignmentsArray[i].prereqs[0].name;  
            }
        }
        else
        {
               prereqs = ""
        }
        

    //build out string
    var markup="";
        markup += "<li class='assignmentLi'>" +
      "<div class='assignmentBox'>" +
         "<div class='divLeft'>" +
            "<a href='" + assignmentsArray[i].url + "'><span class='" + typeClass + "'></span><span id='spanAName'>" + assignmentsArray[i].name+ "</span></a>" +
            "<div id='divPrereq'>" + prereqs + "</div>" +
            "<div class='divDesc'>" + assignmentsArray[i].description + "</div>" +
         "</div>" +
         "<div class='divRight'>" +
            "<span id='spanAStatus' class='" + statusClass + "'></span><span id='divStatusDesc'>" + statusStr + "</span>" +
            "<div class='divPoints'>"+ pointsStr + "</div>" +
            "<div class='divScore'>Score: " + assignmentsArray[i].score + "</div>" +
         "</div>" +
      "</div>" +
   "</li>";
        
        console.log(content.assignments[i].optional);
        if(content.assignments[i].optional == 0)
        {
            $selection = $("#ulAssignments");
            
        }
        else
        {
            $selection = $("#ulOptionalAssignments");
              
        }
        
        //add the html markup to the appropriate ul
        var $li = $selection.append(markup);
    
    }
    //PREREQUISITES
    if(content.prereqs.length>0)
    {
     
        d3.select("#divPrerequisites").attr("class","show");
        
        var liSelect = d3.select("#ulPrerequisites").selectAll("li")
         .data(content.prereqs)
         .enter()
         .append("li")
        .attr("class","prereqLi");


        var originalColor ="";
        liSelect
        .append("a")
        .attr("href",function(d)
              {
                return d.url; 
              })
        .text(function(d)
              {
                return d.mName + " - " + d.aName;  
              });
        
        liSelect
        .on("mouseenter", function(d)
            {
                var mod = d3.select("#" + d.mId);
                originalColor = mod.style("fill");
                mod.style("fill", "#FF6600");

                d3.select("#tooltip")
                .attr("class","seeThroughOnly");
            })
        .on("mouseleave", function(d)
            {
                d3.select(this).style("color","black");
                d3.select("#tooltip")
                    .attr("class","solid");

                var mod = d3.select("#" + d.mId)
                .style("fill", originalColor); 
            });
    }
    else
    {
           d3.select("#divPrerequisites").attr("class","hidden");
    }
    d3.select("#divInnerTooltip")
    .attr("class","showOverflow");


    //add click event to close button of modal popup

    var btn = d3.select("#imgClose");
    btn.on("click", function(){
           //hide the popup
            hideModalPopup();
        });

    
    d3.select("#tooltip")
    .attr("class","solid");
    
    //blocker
    d3.select("#blocker").attr("class","show")
        .on("click", function(){
            hideModalPopup();
        });
    
    //reset scroll
    var div = document.getElementById("divInnerTooltip");
    div.scrollTop = 0;
    div.scrollLeft = 0;
    return true;
}//function
            

/*********************************Show Tooltip********************************/      
/****5/27/2014****/
function showTooltip(d){
    
    resetTooltipContent();
    var tooltip = d3.select("#tooltip");
    //register a mousemove event so the tooltip keeps following the mouse
    tooltip.select("#spTitle")
      .text(d.name);

      d3.select("#tooltipDesc")
      .text(d.description);

        //add assignments and preqrequisites
        //d3.select("ulAssignments")
    var liSelection = d3.select("#ulAssignments").selectAll("li")
     .data(d.assignments)
     .enter()
     .append("li");
    
    liSelection.append("i")
        .attr("class",function(d)
              {
              //Determine the type of assignment icon based on d.assignments.type
              switch(d.type) {
                    case "1"://Reading Quiz
                        return "fa fa-list-ul";
                        break;
                    case "2"://Writing Assignment
                        return "fa fa-pencil";
                        break;
                    case "3"://Game
                        return "fa fa-gamepad";//does not exist
                        break;
                    case "4"://Self assessment
                        return "fa fa-search-plus";
                        break;
                    case "5":
                        return "fa fa-book";
                        break;
                    default:
                        return "fa fa-pencil";
                } 
              });
    liSelection.append("a")
            .attr("href", function(d){return d.url})
            .append("span")
    .html(function(d)
          {
            return d.name;  
          });
        
        //HERE WE NEED TO FIND ALL THE DISTINCT MODULES THAT ARE PRE-REQS TO THE CURRENT MODULE
        var allPrereqs = d.prereqs;
    
    if(allPrereqs.length>0)
    {
        d3.select("#divPrerequisites").attr("class","show");
        var singleList, modules=new Array(),currentMod, holderMod="";
        for (var i = 0; i < allPrereqs.length; i++) {
            currentMod = allPrereqs[i];
            if(currentMod!=holderMod)
            {
                //we entered a new module 
                holderMod = currentMod;
                modules.push(holderMod);
            }
        }
    
        var ulPreqreqs = d3.select("#ulPrerequisites").selectAll("li")
         .data(modules)
         .enter()
         .append("li");
    
        ulPreqreqs.append("a")
             .text(function(d){return d.mName;});
        
    }
    else
    {
        d3.select("#divPrerequisites").attr("class","hidden");   
    }
    breadCrumbFill(d);
    
        
    //Show the tooltip
    d3.select("#tooltip")
    .attr("class","transparent");
    
    d3.select("#divInnerTooltip")
    .attr("class","hideOverflow");
    
    //reset scroll
    var div = document.getElementById("divInnerTooltip");
    div.scrollTop = 0;
    div.scrollLeft = 0;
    
    
    return true;
}

/***************************************** tooltip to follow mouse on each movement ****************************************/
            
function followCursor(){
    var tooltip = d3.select("#tooltip");
    //find width and height of tooltip and of the document.
    //if the natural x or y positions make it so the box goes outside of "margins" then choose a different x/y positions
    var tipWidth = parseInt(tooltip.style("width"), 10);
    var tipHeight = parseInt(tooltip.style("height"),10);

    var docWidth = parseInt(document.documentElement.clientWidth);
    var docHeight = parseInt(document.documentElement.clientHeight);

    //Get x/y values for the tooltip
    var wrap = d3.select("#wrapper").node();
    var x = parseInt((d3.mouse(wrap)[0]) + 20);
    var y = parseInt((d3.mouse(wrap)[1]) + 20);

    //when running into the right frame
    //only do this if the entire document width isn't smaller than the tooltip width
    if(docWidth>tipWidth)
    {
        var avaWidth = docWidth-x;
        if((avaWidth)<tipWidth)
        {
            x = x-avaWidth;
        }
    }
    //when running into the bottom frame
    //only do this if the entire document height isn't smaller than the tooltip height
    if(docHeight>tipHeight)
    {
        var avaHeight = docHeight-y;
        if((avaHeight)<(tipHeight/2))
        { 
            if(y-tipHeight >0)
                {
                    /*
                console.log("docHeight: " + docHeight);
                console.log("old y: " + y);
                console.log("height available: " + avaHeight);

                console.log("tipHeight: " + tipHeight);*/
                y = y-tipHeight-50;

                //console.log("new x: " + x);
            }
        }
    }

    $("#tooltip").offset({left:x, top:y});
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
          .filter(function(node) {
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
			var breadCrumb = [['width'],['points'],['name'],['path']];

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
					.data(path, function(d) { return d.name + d.depth; });

				// Add breadcrumb and label for entering nodes.
				var entering = g.enter().append("svg:g");

				entering.append("svg:polygon")
					.attr("points", breadcrumbPoints)
                
                    .style("fill", function(d) { return d.color; })
                    .style("stroke","white");
                
                //make the current polygon a different color
                //var lastPolygon = d3.select(d3.selectAll("polygon")[0].pop());
                //lastPolygon.style("fill","#fff")

				entering.append("svg:text")
					.attr("x", (b.w + b.t) / 2)
					.attr("y", b.h / 2)
					.attr("dy", "0.35em")
					.attr("text-anchor", "middle")
					.text(function(d) { 
                        
                        var name = d.name;
                        if(name.length>22)
                        {
                            name = name.substring(0,19) + "...";   
                        }
                        return name; 
                    });
                
                //var lastText = d3.select(d3.selectAll("text")[0].pop());
                //lastText.style("fill","#2C2F34");

				// Set position for entering and updating nodes.
				g.attr("transform", function(d, i) {
					return "translate(" + i * (b.w + b.s) + ", 0)";
				});

				// Remove exiting nodes.
				g.exit().remove();

				// Make the breadcrumb trail visible, if it's hidden.
				d3.select("#trail")
				.style("visibility", "")
                .style("overflow","visible");
                return true;
			}
                   
            });

//catch escape event for when modal window is showing
$(document).keyup(function(e) {   // enter   
  if (e.keyCode == 27) { 
    if($("#blocker").hasClass("show"))
    { 
        hideModalPopup();
    }
  }
});

/*******************************Close Modal Popup*************************/
function hideModalPopup(content)
{
    d3.select("#tooltip").attr("class","hidden");
    d3.select("#blocker").attr("class","hidden"); 
    
    //remove assignments and pre-reqs from modal popup
    resetTooltipContent();
    return true;
}

 
/*******************************Reset Tooltip Content*************************/        
function resetTooltipContent()
{
        
    var tooltip = document.getElementById("tooltip");
    var temp = d3.select("#ulAssignments").selectAll("li");
    temp.remove();
    temp=d3.select("#ulPrerequisites").selectAll("li");
    temp.remove();   
    return true;
}