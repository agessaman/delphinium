(function() {
  'use strict';

  angular.module('treeApp', ['ui.tree'])
  .controller('treeCtrl', function($scope,$http) {
      alert("here");
      console.log(moduleData);
    $scope.data  = moduleData;
    $scope.contentClass = "hidden";
//    $scope.modalShown = false;
    $scope.currentModuleItemId = 0;
    $scope.contentId = 0;

    $scope.isExpanded = false;
    $scope.showTags = false;
  
  
    /*
     * ***********************  Functions  ***********************
     */
    $scope.showContent = function(item)
    {
        $scope.isExpanded = !$scope.isExpanded;

        var node = document.getElementById("div"+item.moduleId);
        var currClassName = node.className;

        var nodes = document.getElementsByClassName("node");
        for(var i=0;i<=nodes.length-1;i++)
        {
            nodes[i].className = "hidden node";
        }

        if(currClassName==="visible node")
        {
            node.className = "hidden node";
        }
        else
        {
            node.className = "visible node";
        }
    };

    $scope.showTagsFunc = function(scope){
        this.showTags = !this.showTags;
        var itemId = (scope.item.module_item_id);
        //show/hide div
        var node = document.getElementById("div"+itemId);
        var currClassName = node.className;

        var nodes = document.getElementsByClassName("tagNode");
        for(var i=0;i<=nodes.length-1;i++)
        {
            nodes[i].className = "hidden tagNode";
        }

        if(currClassName==="visible tagNode")
        {
            node.className = "hidden tagNode";
        }
        else
        {
            node.className = "visible tagNode";
            var arr = scope.item.tags;
            if(arr.length>0)
            {
                $scope.tags = arr.split(", ");
            }
            else
            {
                $scope.tags = [];
            }
            var diff = findDifference(avTags,$scope.tags);
            $scope.possibleTags = diff;
        }
        
        
     
     
    }
    
   $scope.treeOptions = {
       accept:function(sourceNodeScope, destNodesScope)
        {
            //if the module is unpublished it can't be dropped to the top position
            if((!destNodesScope.$nodeScope) &&(sourceNodeScope.$parentNodeScope) && (sourceNodeScope.$modelValue.published === "0"))
            {
                //show flash message explainig what is going on
                var msg = document.getElementById("flashMsg");
                msg.className = "visible";
//                        $(selector for your message).slideDown(function() {
//                            setTimeout(function() {
//                                $(selector for your message).slideUp();
//                            }, 5000);
//                        });
                        
                return false;
            }
            else
            {
                return true;
            }
       },
      dropped: function(event) {
        //if the item was moved to the first position, this item will now become the parent of all other items
        if((!event.dest.nodesScope.$nodeScope)&&(event.source.nodeScope.$parentNodeScope))
        {
            console.log("attempting to save items");
            var parent = event.dest.nodesScope.$modelValue[0];
            var allOtherItems = event.dest.nodesScope.$modelValue[1];
            $http.post('moveItemToTop', { parent: JSON.stringify(parent),
            modulesArray:JSON.stringify(allOtherItems)})
                .success(function (data,status) {
                    console.log(data);
                    $scope.data = data;
                    $scope.saveOrder($scope);
                })
                .error(function(data) {
                });
        }
        else if(event.source.nodeScope.$parentNodeScope)//if nodeScope.$parentNodeScope is undefined, it means the top element is being dragged,
        //in which case we don't want to save the order
        {
            //just save the order
            $scope.saveOrder($scope);
        }
      }
    };
    
    $scope.remove = function(scope) {
      scope.remove();
    };

    $scope.toggle = function(scope) {
      scope.toggle();
    };

//this function below (or something similar) will be used by Tara when adding new functionality to the manager
    $scope.newSubItem = function(scope) {
      var nodeData = scope.$modelValue;
      nodeData.nodes.push({
        id: nodeData.id * 10 + nodeData.nodes.length,
        title: nodeData.title + '.' + (nodeData.nodes.length + 1),
        nodes: []
      });
    };

    $scope.collapseAll = function() {
      $scope.$broadcast('collapseAll');
    };

    $scope.expandAll = function() {
      $scope.$broadcast('expandAll');
    };
    
    $scope.saveOrder = function(scope)
    {   
        $http.post('saveModules', { courseId: courseId,
            modulesArray:JSON.stringify($scope.data)})
                .success(function (data,status) {
                    console.log("saved Data");
                    console.log(data);
                })
                .error(function(data) {
                });
    };
    
    $scope.addTags = function(scope)
    {
        var content_id = scope.item.content_id;
        var tags = scope.tagValue;  
        var tagArr = tags.split(",");
                                        
        for(var i=0;i<=tagArr.length-1;i++)
        {
            var a = tagArr[i].trim();
            a = capitalizeFirstLetter(a);
            tagArr[i] = a;
        }
        
        $http.post('addTags', { contentId:content_id,
            tags:JSON.stringify(tagArr),courseId:courseId}).
                success(function (data) {
                    $scope.tags = data.split(", ");
                    scope.item.tags = data;
                    $scope.updateAvailableTags();
                });   
        this.tagValue = "";
    };
    
    $scope.addInnerTag = function(scope, tag)
    {
        var arr = [tag];
        var content_id = scope.$parent.item.content_id;
        
        $http.post('addTags', { contentId:content_id,
            tags:JSON.stringify(arr),courseId:courseId}).
                success(function (data) {
                    $scope.tags = data.split(", ");
                    scope.$parent.item.tags = data;
                    $scope.updateAvailableTags();
                });   
        $scope.tagValue = "";
    }
    
    $scope.deleteTag = function(scope, tag)
    {
        var content_id = scope.$parent.item.content_id
        var trimmed = tag.trim();
        
        $http.post('deleteTag', { contentId:content_id, tag:trimmed})
                .success(function (data) {
                    var t = data.split(", ");
                    if (data==="")
                    {
                        scope.$parent.item.tags = data;
                        $scope.tags = [];
                    }
                    else
                    {
                        $scope.tags = t;
                        scope.$parent.item.tags = data;
                    }
                    
                    //need to also update this item's current tags
                    $scope.updateAvailableTags();
                })
                .error(function (data)
                {
                });
    };
    
    $scope.updateAvailableTags = function()
    {
        $http.get("getAvailableTags", {
            params: {
                courseId: courseId
            }
        })
        .success(function (data,status) {
             if(data.length>0)
            {
                avTags = data.split(", ");
            }

            var diff = findDifference(avTags,$scope.tags);
//            if(diff.length>0)
//            {
                $scope.possibleTags = diff;
//            }
        });
    }
  
    //it is crucial that we save this initial order right away
    $scope.saveOrder();//save the new order right away
  });

})();


/* 
 * Additional functions
 */

function capitalizeFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function findDifference(a, b)
{
    var seen = [], diff = [];
    for ( var i = 0; i < b.length; i++)
        seen[b[i].trim()] = true;
    for ( var i = 0; i < a.length; i++)
        if (!seen[a[i].trim()])
            diff.push(a[i].trim());
    return diff;
}