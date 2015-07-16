(function () {

    'use strict';

    angular.module('myApp')
            .controller('BodyCtrl', BodyCtrl);

    BodyCtrl.$inject = ['$scope', 'alertService'];

    function BodyCtrl($scope, alertService) {
        $scope.alerts = alertService.get();
    }
})();