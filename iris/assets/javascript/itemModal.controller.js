/**
 * Created by tjorgensen on 2/24/2015
 */
'use strict';
var ModalInstanceCtrl = function ($scope, $modalInstance, $http, $location, itemIn) {

    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
};

var ModalCtrl = function ($scope, $modal, $log) {
    $scope.open = function (item) {
        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "modalTemp.html",
            controller: "ModalInstanceCtrl",
            resolve: {
                itemIn: function () {
                    return item;
                }
            }
        });

        $log.info('Modal dismissed at: ' + new Date());
    }
};





