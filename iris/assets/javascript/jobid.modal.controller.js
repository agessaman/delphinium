/**
 * Created by tjorgensen on 2/24/2015
 */
'use strict';
//var options = {
//    "backdrop":"static"
//}
var JobModalInstanceCtrl = function ($scope, $modalInstance, $location, itemIn) {

    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {

        $modalInstance.dismiss('cancel');

    };

    $scope.delete = function(scope){
        //alert($scope.item[4].name);
                
        alert('Are you sure you want to permanently delete this module?');

        $modalInstance.dismiss('delete');
    }
    $scope.unpublish = function(scope){
        $modalInstance.dismiss('unpublish');
    }
    $scope.publish = function(scope){
        $modalInstance.dismiss('publish');
    }

};



var ModalJobCtrl = function ($scope, $modal, $log ) {

    $scope.open = function (item) {


        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "modalTemplate.html",
            controller: "JobModalInstanceCtrl",
            resolve: {
                itemIn: function() {
                    return item;
                }
            }
            //http://jsfiddle.net/alexsuch/RLQhh/

        });
        modalInstance.result.then(function (){



        }, function (input) {
            if (input === 'delete'){
                $scope.remove(item);
            }
            //if(input=== 'unpublish'){
            //    item.published=0;
            //}
            //if(input==='publish'){
            //    item.published=1;
            //}
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
};

$(function () {
    $('.treeLine li').on('click', function (e) {
        var children = $(this).find('> ul > li');
        if (children.is(":visible")) children.hide('fast');
        else children.show('fast');
        e.stopPropagation();
    });
});


var DatepickerCtrl = function ($scope) {


    $scope.open = function($event) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.opened = true;
    };

    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks:'false'
    };


    $scope.format = 'dd-MMMM-yyyy';
};


