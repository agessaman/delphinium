<<<<<<< HEAD
/**
 * Created by Tara on 4/18/2015.
 */
'use strict';

var ItemCtrl = function ($scope, $modalInstance, $location, itemIn) {
    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
};

var addItemCtrl = function ($scope, $modal, $log) {
    $scope.open = function (item) {
        $scope.item = item;
=======
var addItemCtrl = function ($scope, $modal, $log, $http) {
        
    $scope.open = function (item) { 
>>>>>>> official/master
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
<<<<<<< HEAD
        });
        $log.info('Modal dismissed at: ' + new Date());
    }
};
=======
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
    console.log(itemIn);
    $scope.moduleItemTypes = moduleItemTypes;
    
    $scope.changedItemType = function(selectedModuleItemType)
    {
        $scope.selectedModuleItemType = null;
        $scope.selectedItem = null;
        $scope.selectedModuleItemType = selectedModuleItemType;
        $http.get("core/getContentByType", {
            params: {
                type: selectedModuleItemType.value
            }
        })
        .success(function (data, status) {
            data['new'] = "new";
            $scope.itemOptions = data;
        });
    };
    
    
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };
    $scope.changedItem = function(selectedItemToAdd)
    {
        $scope.selectedItem = selectedItemToAdd;
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
};
>>>>>>> official/master
