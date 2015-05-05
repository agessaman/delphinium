/**
 * Created by Tara on 4/18/2015.
 */
'use strict';
//var options = {
//    "backdrop":"static"
//}
var addItemCtrl = function ($scope, $modalInstance, $location, itemIn) {

    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {

        $modalInstance.dismiss('cancel');

    };

    $scope.delete = function() {
        //alert($scope.item);

        alert('Are you sure you want to permanently delete this module?');
        //<a href="" class="btn btn-primary pull-right" ng-click="ok()">Update</a>
        //<a href="" class="cancel btn btn-default pull-right" ng-click="cancel()">Close</a>
        $modalInstance.dismiss('delete');
    }
    $scope.unpublish = function(scope){
        $modalInstance.dismiss('unpublish');
    }
    $scope.publish = function(scope){
        $modalInstance.dismiss('publish');
    }

};


var addItemCtrl = function ($scope, $modal, $log ) {

    $scope.open = function (item) {


        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "addItem.html",
            controller: "ModalInstanceCtrl",
            resolve: {
                itemIn: function() {
                    return item;
                }
            }
            //http://jsfiddle.net/alexsuch/RLQhh/

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
