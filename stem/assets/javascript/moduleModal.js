var addModuleCtrl = function ($scope, $modal, $log ) {
    $scope.open = function (item) {
        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "addModalTemplate.html",
            controller: "moduleCtrl",
            resolve: {
                itemIn: function() {
                    return item;
                },
                modules: function()
                {
                    return rawData;
                }
            }
        });
        modalInstance.result.then(function (itemOut){
            $scope.data[0].children.push(itemOut.item);
        }); 
    };
    
};

var moduleCtrl = function ($scope, $modalInstance,$http, $location, itemIn, modules) {
    $scope.newModuleDate = {date: new Date()};
    $scope.modules = modules;
    $scope.item = itemIn;
    
    $scope.addModule = function () {
        var prereqs =[];
        
        for(x in $scope.selectedModulePrereqs)
        {
            prereqs.push($scope.selectedModulePrereqs[x]['id']);
        }
        var date = new Date($scope.newModuleDate.date).toISOString();
        $http.post('roots/addModule', {
            name: $scope.newModuleName,
            unlock_at: date,
            prerequisites: prereqs,
            published:true
        })
        .success(function (data) {
            $scope.newItem = {
                   item: data
            };
            $modalInstance.close($scope.newItem);
        });
    };
    
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
};






