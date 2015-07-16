var ModalJobCtrl = function ($scope, $modal, $log) {
    $scope.open = function (item) {
        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "modalTemplate.html",
            controller: "JobModalInstanceCtrl",
            resolve: {
                rawModules: function()
                {
                    return rawData;
                },
                itemIn: function () {
                    return item;
                },
                completionRequirementTypes:function(){
                    return completionRequirementTypes;
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


var JobModalInstanceCtrl =function ($scope, $modalInstance,$http, $location, itemIn, rawModules, completionRequirementTypes) {
    
    $scope.filterModuleOptions = function(modules,currentPrereqs)
    {
        for(var i=0;i<=currentPrereqs.length-1;i++)
        {
            var current = currentPrereqs[i];
            var ob = modules.filter(function (ob)
                {
                    if (ob['id'] ===current)
                    {
                        return ob;
                    }
                })[0];

                if (ob !== undefined) {
                    modules.splice(modules.indexOf(ob), 1);

                }
        }
        return modules;
    };
    
    $scope.cancel = function () 
    {
        $modalInstance.dismiss('cancel');
    };
    
    $scope.delete = function () 
    {
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
        var newPrereqs = $scope.prerequisiteIds;
        for(var i=0;i<= newPrereqs.length - 1; i++) {
            if(newPrereqs[i] === item) {
               newPrereqs.splice(i, 1);
            }
        }
        $scope.rawModules = clone(rawModules);
        $scope.prerequisiteIds = newPrereqs;
        $scope.prerequisiteArr = getModules(newPrereqs, $scope.rawModules);
        $scope.moduleOptions = $scope.filterModuleOptions($scope.rawModules, newPrereqs);
        
        $http.post('updateModulePrerequisites', {
            module_id: itemIn['module_id'],
            current_prerequisites_ids: newPrereqs
        })
        .success(function (data) {
            var i =0;
        }).error(function (data, status) {
            alert("An error occurred when removing the prerequisite");
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

    $scope.prereqAdded = function(selectedModulePrereqs)
    {
        $scope.prerequisiteIds.push(selectedModulePrereqs[0]['id']);
        $scope.prerequisiteArr = $scope.prerequisiteArr.concat(selectedModulePrereqs);
        $scope.moduleOptions = $scope.filterModuleOptions($scope.moduleOptions,[selectedModulePrereqs[0]['id']]);
        
        var current_prereq_ids=[];
        for(var x in $scope.prerequisiteArr)
        {
            current_prereq_ids.push($scope.prerequisiteArr[x]['id']);
        }
        $http.post('updateModulePrerequisites', {
            module_id: itemIn['module_id'],
            current_prerequisites_ids: current_prereq_ids
        })
        .success(function (data) {
        }).error(function (data, status) {
            //TODO: change the type of alert to an angular one
            alert("An error occurred when adding the prerequisite "+selectedModulePrereqs[0]['value']);
        });
    };
    $scope.newRequirement = function()
    {
        $scope.requirements.push({id:$scope.counter++});
    };
    $scope.moduleItemRequirementAdded = function(moduleItem, itemId)
    {//0=must_view; 1=must_contribute;2=must_submit
        var types = clone(completionRequirementTypes);
        var result=[];
        switch(moduleItem.type)
        {
            case 'Assignment':
                result.push(types[0]);
                result.push(types[1]);
                result.push(types[2]);
                break;
            case 'File':
                result.push(types[0]);
                break;
            case 'Page':
                result.push(types[0]);
                result.push(types[1]);
                break;
            case 'Discussion':
                result.push(types[0]);
                result.push(types[1]);
                break;
            case 'Quiz':
                result.push(types[0]);
                result.push(types[2]);
            case 'SubHeader':
                result.push(types[0]);
                break;
            case 'ExternalUrl':
                result.push(types[0]);
                break;
            case 'ExternalTool':
                result.push(types[0]);
                break;
        }
        var str = "completionRequirementTypes"+itemId;
        $scope[str] = result;
    };
    
    $scope.updateModuleItem = function()
    {
        
    };
    $scope.actionChanged = function(action, itemId)
    {
        //roots/updateModuleItem
        switch(action.value)
        {
            case "must_view":
                break;
            case "must_contribute":
                break;
            case "must_submit":
                var str = "showScore"+itemId;
                $scope[str] = !$scope[str];
                break;
        }
    };
    
    $scope.init = function()
    {
        $scope.counter = 0;
        $scope.rawModules = clone(rawModules);
        $scope.item = itemIn;
        $scope.module_items = itemIn['module_items'];
        $scope.requirements = [];
//        $scope.showItems = false;
        $scope.moduleName = itemIn['name'];
        if(itemIn['unlock_at']!==undefined)
        {
            $scope.editModuleLock = true;
            $scope.editModuleDate = {date: new Date(itemIn['unlock_at'])};
        }
        else
        {
            $scope.editModuleLock = false;
            $scope.editModuleDate = {date: new Date()};
        }


        var currentPrereqIds = itemIn['prerequisite_module_ids'].split(", ");
        $scope.prerequisiteIds = currentPrereqIds;
        $scope.prerequisiteArr = getModules(currentPrereqIds, $scope.rawModules);//ids and names
        var temp = $scope.rawModules;
        $scope.moduleOptions = $scope.filterModuleOptions(temp, currentPrereqIds);
    };
    
    //init modal window
    $scope.init();
    
    
    /*Utility functions*/
    function deletePrerequisiteFromArray(prerequisiteId, moduleArr)
    {
        var arr = moduleArr.filter(function (ob)
                {
                    if (ob['id']!=prerequisiteId)
                    {
                        return ob;
                    }
                })
        return arr;
    }
    
    function getModules(prereqsIds, modules)
    {
        var temp = modules;
        var result = new Array();
        for(x in prereqsIds)
        {
            var ob = temp.filter(function (ob)
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


function clone(obj){
    if(obj == null || typeof(obj) != 'object')
        return obj;

    var temp = new obj.constructor(); 
    for(var key in obj)
        temp[key] = clone(obj[key]);

    return temp;
}