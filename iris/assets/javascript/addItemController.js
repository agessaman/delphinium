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
        var modalInstance = $modal.open({
            templateUrl: "addItem.html",
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
