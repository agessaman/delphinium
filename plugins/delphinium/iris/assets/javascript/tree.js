(function() {
  'use strict';

  angular.module('treeApp', ['ui.tree', 'ngTagsInput'])
  .controller('treeCtrl', function($scope,$http) {
      
    $scope.data  = moduleData;
    $scope.contentClass = "hidden";
    $scope.modalShown = false;
    $scope.currentModuleItemId = 0;
    $scope.contentId = 0;

    $scope.isExpanded = false;
    $scope.showTags = false;
  
    $scope.showTagsFunc = function(scope){
        this.showTags = !this.showTags;
        
        var itemId = (scope.item.module_item_id);
        if(itemId !== $scope.currentModuleItemId && this.showTags)
        {
            
            var arr = scope.item.tags;
            if(arr.length>0)
            {
                $scope.tags = arr.split(",");
            }
            else
            {
                $scope.tags = [];
            }
            $scope.currentModuleItemId = itemId;
        }
        
        
//        $scope.contentId = 0;
//        
//        if(($scope.tags===undefined) || ($scope.tags.length<1))
//        {
//        }
        
        $scope.possibleTags = avTags;
    }
    
    $scope.callbacks = {
//        
//       dragMove: function(event){
//           console.log("moved"); 
//       },
        dropped: function(event) {
           console.log("dropped");
        }


    };
    $scope.remove = function(scope) {
      scope.remove();
    };

    $scope.toggle = function(scope) {
      scope.toggle();
    };

    $scope.moveLastToTheBeginning = function () {
      var a = $scope.data.pop();
      $scope.data.splice(0,0, a);
    };

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


    $scope.getContent = function(scope) {
        console.log("showContent");
        var id = scope.$nodeScope.$modelValue.moduleId;
        var curClass = document.getElementById("div"+id);
        if (curClass.className === "hidden")
          curClass.className = "visible";
        else
          curClass.className = "hidden";
    };
    
    $scope.saveOrder = function(scope)
    {   console.log($scope.data);
//        return;
        $http.post('saveModules', { courseId: courseId,
            modulesArray:JSON.stringify($scope.data)})
                .success(function (data,status) {
                    console.log(data);
                })
                .error(function(data) {
                    console.log(data);
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
        
        //TODO: after updating tags, return an updated list of possible tags
        
        $http.post('addTags', { contentId:content_id,
            tags:JSON.stringify(tagArr),courseId:courseId}).
                success(function (data) {
                    $scope.tags = data.split(",");
                    scope.item.tags = data.split(",");
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
                    $scope.tags = data.split(",");
                    scope.$parent.item.tags = data.split(",");
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
                    var t = data.split(",");
                    if (data==="")
                    {
                        $scope.tags = [];
                    }
                    else
                    {
                        $scope.tags = t;
                    }
                })
                .error(function (data)
                {
                    console.log(data);
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
                avTags = data.split(",");
            }

            $scope.possibleTags = avTags;
        });
    }
  });

})();



function capitalizeFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}