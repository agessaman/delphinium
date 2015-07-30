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
                    var ob = rawData.filter(function (ob)
                    {
                        if (ob.id === "0")
                        {
                            return ob;
                        }
                    })[0];

                    if (ob === undefined) {
                        var none = {id:"0", value:"[None]"};
                        rawData.unshift(none);
                    }
                    return rawData;
                },
                parent_id: function()
                {
                    return $scope.data[0]['module_id'];
                }
            }
        });
        modalInstance.result.then(function (itemOut){
            $scope.data[0].children.push(itemOut.item);
        }); 
    };
    
};

var moduleCtrl = function ($scope, $modalInstance,$http, $location, itemIn, modules, parent_id) {
    $scope.newModuleDate = {date: new Date()};
    $scope.modules = modules;
    $scope.item = itemIn;
    
    $scope.addModule = function () {
        var prereqs =[];
        
        for(x in $scope.selectedModulePrereqs)
        {
            prereqs.push($scope.selectedModulePrereqs[x]['id']);
        }
        var date;
        if($scope.newModuleLock === true)
        {
            date = new Date($scope.newModuleDate.date).toISOString();
        }
        else
        {
            date = null;
        }
        $http.post('roots/addModule', {
            name: $scope.newModuleName,
            unlock_at: date,
            prerequisites: prereqs,
            published:true,
            parent_id:parent_id
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






