var addItemCtrl = function ($scope, $modal, $log, $http) {
        
    $scope.open = function (item) { 
        var modalInstance = $modal.open({
            templateUrl: "addItem.html",
            controller: "ModalInstanceCtrl",
            resolve: {
                itemIn: function () {
                    return item;
                },
                moduleItemTypes: function()
                {
                    return $scope.moduleItemTypes;
                }
            }
        });
        
        modalInstance.result.then(function () {
        }, function () {
            console.log("hello");
            $log.info('Modal clooooosed at: ' + new Date());
        });
          
        $log.info('Modal opened at: ' + new Date());
        
    };
};




var ModalInstanceCtrl = function ($scope, $modalInstance, $location, $http, itemIn, moduleItemTypes) {
    $scope.item = itemIn;
    $scope.moduleItemTypes = moduleItemTypes;
    
    $scope.changedItemType = function(selectedModuleItemType)
    {
        $scope.resetPartials();
        $scope.selectedModuleItemType = null;
        $scope.selectedItem = null;
        $scope.selectedModuleItemType = selectedModuleItemType;
        $http.get("core/getContentByType", {
            params: {
                type: selectedModuleItemType.value
            }
        })
        .success(function (data, status) {
            var newItem = { 'id': 'new', 'name': '[new item]' };
            data[0] = newItem;
            $scope.itemOptions = data;
        });
    };
    
    
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };
    
    $scope.changedItem = function(selectedItemToAdd)
    {
        $scope.resetPartials();
        var itemToAdd = selectedItemToAdd[0];
        if(itemToAdd.id === "new")
        {
            var type = $scope.selectedModuleItemType.value;
            switch(type) {
                case "Assignment":
                    $scope.newAssignment = true;
                    break;
                case "Quiz":
                    $scope.newQuiz = true;
                    break;
                case "SubHeader":
                    $scope.newSubHeader = true;
                    break;
                case "File":
                    $scope.newFile = true;
                    break;
                case "Page":
                    $scope.newPage = true;
                    break;
                case "Discussion":
                    $scope.newDiscussion = true;
                    break;
                case "ExternalUrl":
                    $scope.newExternalUrl = true;
                    break;
                case "ExternalTool":
                    $scope.newExternalTool = true;
                    break;
//                default:
//                    default code block
            }
            
        }
        else
        {
            $scope.selectedItem = selectedItemToAdd;
        }
    };
    
    $scope.addItem = function()
    {
        $http.post('core/addModuleItem', {
            name:$scope.selectedItem.name,
            id:parseInt($scope.selectedItem.id),
            module_id: itemIn.module_id,
            type: $scope.selectedModuleItemType.value,
            url:$scope.selectedItem.url
            
        }).
        success(function (data) {
            $modalInstance.dismiss('cancel');
        });
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
    
    $scope.resetPartials = function()
    {
        $scope.newAssignment = false;
        $scope.newQuiz = false;
        $scope.newSubHeader = false;
        $scope.newFile = false;
        $scope.newPage = false;
        $scope.newDiscussion = false;
        $scope.newExternalUrl = false;
        $scope.newExternalTool = false;
    }
};

