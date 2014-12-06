(function() {
  'use strict';

  angular.module('demoApp', ["ui.tree","ngModal"])
  .controller('MainCtrl', function($scope, $timeout, $http) {
      
    // Parameters
    $scope.parameters = {
      dragEnabled: true,
      emptyPlaceholderEnabled: true,
      maxDepth: 100,
      dragDelay: 0,
      dragDistance: 0,
      lockX: false,
      lockY: false,
      boundTo: '',
      spacing: 20,
      coverage: 50,
      cancelKey: 'esc',
      copyKey: 'shift',
      selectKey: 'ctrl',
      enableExpandOnHover: true,
      expandOnHover: 500
    };

    $scope.courseId = courseId;
//    $scope.keys = keys;
    $scope.list = moduleData;
    $scope.contentClass = "hidden";
    $scope.modalShown = false;
    $scope.currentModuleItemId = 0;
    $scope.contentId = 0;
    
    
    $scope.callbacks = {
        
       dragMove: function(event){
           console.log("moved");
//         console.log(event.source.nodeScope.$modelValue);       
       },
        dropped: function(event) {
           console.log("dropped");
//            return $http({
//                method: 'GET',
//                url: 'test'
//            }).then(function ($response) {
//                console.log($response);
//                return $response;
//            });
//            console.log("dropped");
            console.log(event.source.nodeScope.$modelValue);     
        }


    };

    $scope.toggleModal = function() {
        $scope.modalShown = !$scope.modalShown;
    };
    
    $scope.toggle = function(scope) {
      scope.toggle();
    };
      
    $scope.remove = function(scope) {
      scope.remove();
    };
    
     $scope.collapseAll = function() {
      $scope.$broadcast('collapseAll');
    };

    $scope.expandAll = function() {
      $scope.$broadcast('expandAll');
    };
    
    $scope.getTags = function(scope, module_item_id)
    {
        var content_id = scope.item.content_id;
        
        $scope.currentModuleItemId = module_item_id;
        $scope.contentId = content_id;
        $http
        .get('getTags', {
            params: {
                contentId: content_id
            }
         })
         .success(function (data,status) {
            console.log(data);
            var tags;
            if(data===undefined||data.length<=1)
            {}else{
                tags = data.split(",");
            }
            
            $scope.tags = tags;
            
         });
         
          $scope.modalShown = !$scope.modalShown;
        
    };
    
    $scope.saveOrder = function(scope)
    {   
        console.log("save Order");
        $http.post('saveModules', { courseId: courseId,
            modulesArray:JSON.stringify($scope.list)}).
                success(function (data,status) {
                    console.log(data);
                });
    };
    
    $scope.addTags = function(scope)
    {
        $scope.tagValue = '';
        var tags = scope.tagValue;  
        var tagArr = tags.split(",");
                                        
        for(var i=0;i<=tagArr.length-1;i++)
        {
            tagArr[i] = tagArr[i].trim();
        }

        $http.post('addTags', { contentId:$scope.contentId,
            tags:JSON.stringify(tagArr), courseId:courseId}).
                success(function (data) {
                    console.log(data);
                    $scope.tags = data.split(",");
                });   
        
    };
    
    $scope.deleteTag = function(scope, tag)
    {
        var trimmed = tag.trim();
        
        $http.post('deleteTag', { contentId:$scope.contentId, tag:trimmed})
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
    
    $scope.showContent = function(scope) {
        var id = scope.$nodeScope.$modelValue.moduleId;
        var curClass = document.getElementById("div"+id);
        if (curClass.className === "hidden")
          curClass.className = "visible";
        else
          curClass.className = "hidden";
        };
    

    $scope.newSubItem = function(scope) {
      var nodeData = scope.$modelValue;
      nodeData.items.push({
        id: nodeData.id * 10 + nodeData.items.length,
        title: nodeData.title + '.' + (nodeData.items.length + 1),
        items: []
      });
    };
    
  });

})();



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