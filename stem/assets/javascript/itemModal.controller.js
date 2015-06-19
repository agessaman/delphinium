///**
// * Created by tjorgensen on 2/24/2015
// */
//'use strict';
//var ModalInstanceCtrl = function ($scope, $modalInstance,$http, $location, itemIn) {
//
//    $scope.item = itemIn;
//    $scope.ok = function () {
//        $scope.jobData.executeNow = false;
//        $modalInstance.close($scope.item);
//    };
//
//    $scope.cancel = function () {
//        $modalInstance.dismiss('cancel');
//    };
//
//    $scope.delete = function(node) {
//        $http.post('deleteModuleItem', {
//            module_id: node.item.module_id,
//            module_item_id: node.item.module_item_id
//        }).
//        success(function (data) {
//            $scope.data = data;
//                console.log("success");
//        });
//
//        $modalInstance.dismiss('delete');
//    };
//
//    $scope.unpublish = function(scope){
//        $modalInstance.dismiss('unpublish');
//    };
//
//    $scope.publish = function(scope){
//        $modalInstance.dismiss('publish');
//    };
//
//};
//
//
//var ModalCtrl = function ($scope, $modal, $log ) {
//
//    $scope.open = function (item) {
//
//
//        $scope.item = item;
//        var modalInstance = $modal.open({
//            templateUrl: "modalTemp.html",
//            controller: "ModalInstanceCtrl",
//            resolve: {
//                itemIn: function() {
//                    return item;
//                }
//            }
//
//        });
//        modalInstance.result.then(function (input){
//            //alert(input);
//            $log.info('testing' + input);
//
//        }, function (input) {
//            if (input === 'delete'){
//                $scope.remove(item);
//            }
//            if(input=== 'unpublish'){
//                item.published=0;
//            }
//            if(input==='publish'){
//                item.published=1;
//            }
//            $log.info('Modal dismissed at: ' + new Date());
//        });
//    };
//};
//
//
//
//
//
/**
 * Created by tjorgensen on 2/24/2015
 */
'use strict';
//var options = {
//    "backdrop":"static"
//}
var ModalInstanceCtrl = function ($scope, $modalInstance, $location, itemIn) {

    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {

        $modalInstance.dismiss('cancel');

    };
    $scope.showTagsFunc = function (scope) {
        this.showTags = !this.showTags;
        var itemId = (scope.item.module_item_id);
        //show/hide div
        var node = document.getElementById("div" + itemId);
        var currClassName = node.className;

        var nodes = document.getElementsByClassName("tagNode");
        for (var i = 0; i <= nodes.length - 1; i++) {
            nodes[i].className = "hidden tagNode";
        }

        if (currClassName === "visible tagNode") {
            node.className = "hidden tagNode";
        }
        else {
            node.className = "visible tagNode";
            var arr = scope.item.content[0].tags;
            if (arr.length > 0) {
                $scope.tags = arr.split(", ");
            }
            else {
                $scope.tags = [];
            }
            var diff = findDifference(avTags, $scope.tags);
            $scope.possibleTags = diff;
        }


    };


    $scope.delete = function(scope){
        //alert($scope.item[4].name);

        alert('Are you sure you want to permanently delete this module?');

        $modalInstance.dismiss('delete');
    }
};


var ModalCtrl = function ($scope, $modal, $log ) {

    $scope.open = function (item) {


        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "modalTemp.html",
            controller: "JobModalInstanceCtrl",
            resolve: {
                itemIn: function() {
                    return item;
                }
            }

        });
        modalInstance.result.then(function (){



        }, function (input) {
            if (input === 'delete'){
                $scope.remove(item);
            }
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
};




