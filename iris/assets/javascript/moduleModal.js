/**
 * Created by Tara on 3/28/2015.
 */

'use strict';
//var options = {
//    "backdrop":"static"
//}
var moduleCtrl = function ($scope, $modalInstance, $location, itemIn) {

    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };

    //$scope.addModule = function(){
    //    $modalInstance.dismiss('addModule');
    //    alert('Are you sure you want to permanently add this module?');
    //    $scope.remove();
    //}
};

var addModalCtrl = function ($scope, $modal, $log ) {
    //$scope.saveModule=function(addModuleName){
    //
    //}
    $scope.open = function (item) {
        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "addModalTemplate.html",
            controller: "JobModalInstanceCtrl",
            resolve: {
                itemIn: function() {
                    return item;
                }
            }
        });
        modalInstance.result.then(function (){

        }, function (input) {
            if (input === 'addModule'){
                $scope.addSubItem();
            }
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
};





