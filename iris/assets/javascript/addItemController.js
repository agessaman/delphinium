var addItemCtrl = function ($scope, $modal, $log, $http) {
        
    $scope.open = function (item) { 
        var modalInstance = $modal.open({
            templateUrl: "addItem.html",
            controller: "ModalInstanceCtrl",
            resolve: {
                itemIn: function () {
                    return item;
                },
                moduleItemTypes: function()
                {
                    return $scope.moduleItemTypes;
                }
            }
        });
        
        modalInstance.result.then(function (itemOut) {
            console.log(itemOut);
            item.module_items.push(itemOut);
        }, function () {
        });
          
    };
};




var ModalInstanceCtrl = function ($scope, $window, $modalInstance, $location, $http,$q, itemIn, moduleItemTypes) {
    $scope.item = itemIn;
    $scope.moduleItemTypes = moduleItemTypes;
    $scope.moreOptions = {
                    value: false
                };;
    $scope.changedItemType = function(selectedModuleItemType)
    {
        $scope.resetPartials();
        $scope.selectedModuleItemType = selectedModuleItemType;
        $http.get("core/getContentByType", {
            params: {
                type: selectedModuleItemType.value
            }
        })
        .success(function (data, status) {
            var newItem = { 'id': 'new', 'name': '[new item]' };
            data[0] = newItem;
            $scope.itemOptions = data;
        });
    };
    
    $scope.changedItem = function(selectedItemToAdd)
    {
        $scope.resetPartials();
        var itemToAdd = selectedItemToAdd[0];
        if(itemToAdd.id === "new")
        {
            $scope.newItem = true;
            var type = $scope.selectedModuleItemType.value;
            switch(type) {
                case "Assignment":
                    $scope.newAssignment = true;
                    $scope.assignmentDate = {newAssignmentDueDate : new Date()}
                    break;
                case "Quiz":
                    $scope.newQuiz = true;
                    $scope.quizDate= {newQuizDueDate : new Date()};
                    break;
                case "SubHeader":
                    $scope.newSubHeader = true;
                    break;
                case "File":
                    $scope.newFile = true;
                    break;
                case "Page":
                    $scope.newPage = true;
                    $scope.getPageEditingRoles();
                    break;
                case "Discussion":
                    $scope.newDiscussion = true;
                    $scope.newDiscussionStartDate = new Date();
                    $scope.newDiscussionEndDate = new Date();
                    break;
                case "ExternalUrl":
                    $scope.newExternalUrl = true;
                    break;
                case "ExternalTool":
                    $scope.newExternalTool = true;
                    break;
//                default:
//                    default code block
            }
            
        }
        else
        {
            $scope.selectedItem = selectedItemToAdd[0];
        }
    };
    
    $scope.addNewItem = function()
    {
        if($scope.newPage)
        {
            $scope.addNewPage();
        }
        else if ($scope.newAssignment)
        {
            $scope.addNewAssignment();
        }
        else if ($scope.newQuiz)
        {
            $scope.addNewQuiz();
        }
        else if ($scope.newSubHeader)
        {
            
        }
        else if($scope.newFile)
        {
            $scope.addNewFile();
        }
        else if($scope.newDiscussion)
        {
            $scope.addNewDiscussionTopic();
        }
        else if($scope.newExternalUrl)
        {
            $scope.addNewExternalUrl();
        }
        else if ($scope.newExternalTool)
        {
            $scope.addNewExternalTool();
        }
        
        $modalInstance.dismiss('cancel');
    };
    
    $scope.addNewFile = function()
    {
        var fileName = $scope.newFileUp.name;
        $http.post('uploadFile', {
            name:fileName,
            size:$scope.newFileUp.size,
            content_type:$scope.newFileUp.content_type
        })
        .success(function (data) {
            console.log(data);
//            now we have to upload the file to the upload_url given by Canvas
            $http.post('uploadFileStepTwo',{
                params: data.upload_params,
                upload_url:data.upload_url,
                file:fileName
            }).success(function(data)
            {
                $http.post('uploadFileStepThree',{
                    location: data.location
                }).success(function(data)
                {
                    console.log(data);
                });
            });
             
        });
    };
    
    $scope.addNewPage = function()
    {
        $http.post('addNewPage', {
            title:$scope.newPageTitle,
            pageEditingRole: $scope.selectedPageEditingRole.value,
            body:"hello, testing",
            notifyOfUpdate: $scope.newPageNotify
        })
        .success(function (data) {
            //add the newly created item as a module item to the module
            $scope.newItem = false;
            $scope.addItem(data.title, data.page_id, itemIn.module_id, $scope.selectedModuleItemType.value, data.url);
        });
    };
    
    $scope.addNewDiscussionTopic = function()
    {
        
        $http.post('addNewDiscussionTopic', {
            title:$scope.newDiscussionTopic,
            message:"hello, testing",
            threaded:$scope.newDiscussionThreaded,
            delayed_post_at:new Date($scope.newDiscussionStartDate),
            lock_at:new Date($scope.newDiscussionEndDate),
            podcast_enabled: $scope.newDiscussionPodcast,
            require_initial_post: $scope.newDiscussionMustPost,
            podcast_has_student_posts: true,
            is_announcement:$scope.newDiscussionAnnouncement
        })
        .success(function (data) {
            if(data.subscription_hold!=="topic_is_announcement")
            {
        //add the newly created item as a module item to the module
                $scope.newItem = false;
                $scope.addItem(data.title, data.id, itemIn.module_id, $scope.selectedModuleItemType.value, data.url);
            }
            
        });
    };
    
    $scope.addNewAssignment = function(addModuleItem)
    {
        var deferred = $q.defer();
        var date = new Date($scope.assignmentDate.newAssignmentDueDate).toISOString();
        $http.post('addNewAssignment', {
            name:$scope.newAssignmentName,
            points:$scope.newAssignmentPoints,
            due_at:date
        })
        .success(function (data) {
            
            $scope.newItem = false;
            var itemPromise = $scope.addItem(data.name, data.id, itemIn.module_id, $scope.selectedModuleItemType.value, data.html_url);

             //add the newly created item as a module item to the module
            if(!addModuleItem)
            {//when the module item is added return the promise of the new assignment
                itemPromise.then(function(resolve){
                    deferred.resolve(data);
                }, function(reject){
                    console.log(reject);      
                });
            }
        });
        
        return deferred.promise;
    };
    
    $scope.addNewQuiz = function(addModuleItem)
    {
        var deferred = $q.defer();
        var date = new Date($scope.quizDate.newQuizDueDate).toISOString();
        $http.post('addNewQuiz', {
            title:$scope.newQuizTitle,
            due_at:date
        })
        .success(function (data) {//add the newly created item as a module item to the module
            $scope.newItem = false;
            var itemPromise = $scope.addItem(data.title, data.id, itemIn.module_id, $scope.selectedModuleItemType.value, data.html_url);
            
            if(!addModuleItem)
            {
                itemPromise.then(function(resolve){
                    deferred.resolve(data);
                }, function(reject){
                    console.log(reject);      
                });
            }
        });
        return deferred.promise;
    };
    
    $scope.fileNameChanged = function(ele)
    {
        var files = ele.files;
        var fileUp;
        for (var i=0;i<=ele.files.length-1;i++)
        {
            fileUp = { 'name': files[i].name ,'size':files[i].size,'content_type':files[i].type};
        };
        
        $scope.newFileUp = (fileUp)?fileUp:null;
        $scope.$apply();
    };
    
    $scope.addItem = function(name, itemId, moduleId, type, url)
    {
        if($scope.newItem)
        {
            $scope.addNewItem();
        }
        else
        {
            var deferred = $q.defer();//return a promise of when the item is added
            //optional params; if not provided, we will select them from scope
            name=name||$scope.selectedItem.name;
            itemId = itemId ||parseInt($scope.selectedItem.id);
            moduleId = moduleId||itemIn.module_id;
            type = type ||$scope.selectedModuleItemType.value;
            url = url ||$scope.selectedItem.url;
            
            $http.post('core/addModuleItem', {
                name:name,
                id:itemId,
                module_id: moduleId,
                type:type,
                url:url
            }).
            success(function (data) {
                $scope.newItemOut = {
                    item: data
                };
                deferred.resolve(data);
                $modalInstance.close($scope.newItemOut.item);
            });
        }
        
        return deferred.promise;
    };

    $scope.addNewPage = function()
    {   
       $http.post('addNewPage', {
            title:$scope.newPageTitle,
            body:"test body",//parseInt($scope.selectedItem.id),
            pageEditingRole: $scope.selectedPageEditingRole.value,
            notifyOfUpdate: $scope.newPageNotify.value
        }).
        success(function (data) {
            $scope.newItem = false;
            $scope.addItem(data.title, data.page_id, itemIn.module_id, $scope.selectedModuleItemType.value, data.html_url);
        }); 
    };
    
    $scope.addNewExternalTool = function()
    {
        $http.post('addNewExternalTool', {
            name:$scope.newExternalToolName,
            url:$scope.newExternalToolUrl
        })
        .success(function (data) {
             //add the newly created item as a module item to the module
            $scope.newItem = false;
            $scope.addItem(data.title, data.id, itemIn.module_id, $scope.selectedModuleItemType.value, data.html_url);
        });
    };
    
    $scope.newContent = function()
    {
        
    };
   
    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
    
    $scope.resetPartials = function()
    {
        $scope.newAssignment = false;
        $scope.newQuiz = false;
        $scope.newSubHeader = false;
        $scope.newFile = false;
        $scope.newPage = false;
        $scope.newDiscussion = false;
        $scope.newExternalUrl = false;
        $scope.newExternalTool = false;
    };
    
    $scope.getPageEditingRoles = function()
    {
        $http.get("getPageEditingRoles")
        .success(function (data) {
            $scope.pageEditingRoles = data;
        });
    };
    
    $scope.enableOptions = function()
    {
        $scope.moreOptions.value = true;  
    };
    
    $scope.moreOptions = function()
    {
        if ($scope.newAssignment)
        {
            var newAssignPromise =  $scope.addNewAssignment(false);

            newAssignPromise.then(function(resolve){
                var url = "assignments/"+resolve.id+"/edit";
                $modalInstance.dismiss('cancel');
                $window.open(lmsUrl+url);
            }, function(reject){
                console.log(reject);      
            });
    
    
        }
        else if ($scope.newQuiz)
        {
            var newQuizPromise =  $scope.addNewQuiz(false);
            newQuizPromise.then(function(resolve){
                var url = "quizzes/"+resolve.id+"/edit";
                $modalInstance.dismiss('cancel');
                $window.open(lmsUrl+url);
            }, function(reject){
                console.log(reject);      
            });
            
        }
    };
};

