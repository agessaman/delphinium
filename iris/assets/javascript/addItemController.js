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
        
        modalInstance.result.then(function () {
        }, function () {
        });
          
    };
};




var ModalInstanceCtrl = function ($scope, $modalInstance, $location, $http, itemIn, moduleItemTypes) {
    $scope.item = itemIn;
    $scope.moduleItemTypes = moduleItemTypes;
    
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
    
    
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
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
                    $scope.getAssignmentGroups();
                    break;
                case "Quiz":
                    $scope.newQuiz = true;
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
            
        }
        else if ($scope.newQuiz)
        {
            
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
            
        }
        $modalInstance.dismiss('cancel');
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
    $scope.addNewAssignment = function()
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
             //add the newly created item as a module item to the module
            $scope.newItem = false;
            $scope.addItem(data.title, data.id, itemIn.module_id, $scope.selectedModuleItemType.value, data.url);
        });
    };
    
    $scope.addNewFile = function()
    {
        $http.post('uploadFile', {
            name:$scope.newFileUp.name,
            size:$scope.newFileUp.size,
            content_type:$scope.newFileUp.content_type
        })
        .success(function (data) {
            console.log(data);
//            now we have to upload the file to the upload_url given by Canvas
//            $http.post(data.upload_url,{
//                key: data.upload_params.key,
//                acl: data.upload_params.acl,
//                Filename: data.upload_params.Filename,
//                AWSAccessKeyId:data.upload_params.AWSAccessKeyId,
//                Policy:data.upload_params.Policy,
//                Signature:data.upload_params.Signature,
//                'Content-Type':data.upload_params.Content-Type,
//                File:
//            }).success(function(data)
//            {
//                
//            });
             //add the newly created item as a module item to the module
//            $scope.newItem = false;
//            $scope.addItem(data.display_name, data.id, itemIn.module_id, $scope.selectedModuleItemType.value, data.url);
        });
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
                $modalInstance.dismiss('cancel');
            });
        }
        
        
    };

    $scope.addNewPage = function()
    {   
       $http.post('core/addPage', {
                title:$scope.pageTitle,
                body:parseInt($scope.selectedItem.id),
                pageEditingRole: $scope.selectedPageEditingRole,
                notifyOfUpdate: $scope.selectedModuleItemType.value,
                published:$scope.selectedItem.url,
                frontPage:no
            }).
            success(function (data) {
                $modalInstance.dismiss('cancel');
            }); 
    }
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
    
    $scope.getAssignmentGroups = function()
    {
        $http.get("getAssignmentGroups")
        .success(function (data) {
            $scope.assignmentGroups = data;
        });
    };
};

