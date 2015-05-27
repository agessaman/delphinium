/**
 * Created by tjorgensen on 2/24/2015
 */
'use strict';
var ModalInstanceCtrl = function ($scope, $modalInstance,$http, $location, itemIn) {

    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

    $scope.delete = function(node) {
        $http.post('deleteModuleItem', {
            module_id: node.item.module_id,
            module_item_id: node.item.module_item_id
        }).
        success(function (data) {
            $scope.data = data;
                console.log("success");
        });

        $modalInstance.dismiss('delete');
    };

    $scope.unpublish = function(scope){
        $modalInstance.dismiss('unpublish');
    };

    $scope.publish = function(scope){
        $modalInstance.dismiss('publish');
    };

};


var ModalCtrl = function ($scope, $modal, $log ) {

    $scope.open = function (item) {


        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "modalTemp.html",
            controller: "ModalInstanceCtrl",
            resolve: {
                itemIn: function() {
                    return item;
                }
            }

        });
        modalInstance.result.then(function (input){
            //alert(input);
            $log.info('testing' + input);

        }, function (input) {
            if (input === 'delete'){
                $scope.remove(item);
            }
            if(input=== 'unpublish'){
                item.published=0;
            }
            if(input==='publish'){
                item.published=1;
            }
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
};





