var ModalJobCtrl = function ($scope, $modal, $log) {
    $scope.open = function (item) {
        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "modalTemplate.html",
            controller: "JobModalInstanceCtrl",
            resolve: {
                itemIn: function () {
                    return item;
                },
                modules: function()
                {
                    return rawData;
                }
            }
        });
        modalInstance.result.then(function (itemOut){
            if(itemOut.itemId)
            {//deleted module
                for(x in $scope.data[0].children)
                {
                    if($scope.data[0].children[x]['module_id']===itemOut.itemId)
                    {
                        $scope.data[0].children.splice(x, 1);
                    }
                }
            }else{
            $scope.data[0].children.push(itemOut.item);
            }
        }); 
        
        
    };
};


var JobModalInstanceCtrl =function ($scope, $modalInstance,$http, $location, itemIn, modules) {
    $scope.item = itemIn;
    $scope.moduleName = itemIn['name'];
    $scope.modules = modules;
    $scope.editModuleDate = {date: new Date()};
    $scope.modulePrereqs =  new Array();
    
    var prereqs = itemIn['prerequisite_module_ids'].split(",");
    $scope.modulePrereqsIds = prereqs;//ids only
    
    $scope.modulePrereqs = getModules(prereqs, modules);//ids and names
    
    $scope.cancel = function () {

        $modalInstance.dismiss('cancel');

    };

    $scope.delete = function () {
        var x = window.confirm('Are you sure you want to permanently delete this module?');
        if (x){
            $http.post('deleteModule', {
                module_id: itemIn['module_id']
            })
            .success(function (data) {
                $scope.newItem = {
                       itemId: itemIn['module_id']
                };
            $modalInstance.close($scope.newItem);
            });
        }else{
            $modalInstance.dismiss('cancel');
        }
    
    };
    
    $scope.removePrerequisite = function(item)
    {
        var newPrereqs = $scope.modulePrereqsIds;
        for(var i=0;i<= newPrereqs.length - 1; i++) {
            if(newPrereqs[i] === item) {
               newPrereqs.splice(i, 1);
            }
        }
        $http.post('updateModulePrerequisites', {
            module_id: itemIn['module_id'],
            current_prerequisites: newPrereqs
        })
        .success(function (data) {
            $scope.modulePrereqsIds = newPrereqs;
            $scope.modulePrereqs = getModules($scope.modulePrereqsIds, modules);
            
        });
    };
    
    $scope.updateModule = function()
    {
        var date;
        if($scope.editModuleLock === true){
            date = new Date($scope.editModuleDate.date).toISOString();
        }else{
            date = null;
        }
        var newPrereqs = new Array();
        for(x in $scope.selectedModulePrereqs)
        {
            newPrereqs.push($scope.selectedModulePrereqs[x]['id']);
        }
        var totalPrereqs = $scope.modulePrereqsIds.concat(newPrereqs);
        
        $http.post('roots/updateModule', {
            module_id:$scope.item['module_id'],
            name: $scope.moduleName,
            unlock_at: date,
            prerequisites: totalPrereqs,
            published:true
        })
        .success(function (data) {
            $scope.newItem = {
                   item: $scope.item
            };
            $modalInstance.close($scope.newItem);
        });
    };
   
    $scope.unpublish = function (scope) {
        $modalInstance.dismiss('unpublish');
    };
    $scope.publish = function (scope) {
        $modalInstance.dismiss('publish');
    };

    function getModules(prereqsIds, modules)
    {
        var result = new Array();
        for(x in prereqsIds)
        {
            var ob = modules.filter(function (ob)
                {
                    if (ob.id === prereqsIds[x])
                    {
                        return ob;
                    }
                })[0];

                if (ob !== undefined) {
                    result.push(ob);

                }
        }
        
        return result;
    }
};

$(function () {
    $('.treeLine li').on('click', function (e) {
        var children = $(this).find('> ul > li');
        if (children.is(":visible"))
            children.hide('fast');
        else
            children.show('fast');
        e.stopPropagation();
    });
});


var DatepickerCtrl = function ($scope) {
    $scope.open = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.opened = true;
    };

    $scope.dateOptions = {
        formatYear: 'yy',
        startingDay: 1,
        showWeeks: 'false'
    };

    $scope.minDate = new Date();
    $scope.format = 'dd-MMMM-yyyy';
};


