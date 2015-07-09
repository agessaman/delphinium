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
        modalInstance.result.then(function () {
        }, function (input) {
            if (input === 'delete') {
                $scope.remove(item);
            }
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
};


var JobModalInstanceCtrl =function ($scope, $modalInstance,$http, $location, itemIn, modules) {
    $scope.item = itemIn;
    $scope.modules = modules;
    $scope.editModuleDate = {date: new Date()};
    $scope.modulePrereqs =  new Array();
    
    var prereqs = itemIn['prerequisite_module_ids'].split(",");
    $scope.modulePrereqsIds = prereqs;//ids only
    
    $scope.modulePrereqs = getModules(prereqs, modules);//ids and names
    
    
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {

        $modalInstance.dismiss('cancel');

    };

    $scope.delete = function (scope) {
        //alert($scope.item[4].name);

        alert('Are you sure you want to permanently delete this module?');

        $modalInstance.dismiss('delete');
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
        var newPrereqs = new Array();
        for(x in $scope.selectedModulePrereqs)
        {
            newPrereqs.push($scope.selectedModulePrereqs[x]['id']);
        }
        var totalPrereqs = $scope.modulePrereqsIds.concat(newPrereqs);
        
        $http.post('core/updateModule', {
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


