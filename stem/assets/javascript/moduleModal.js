/*
 * Copyright (C) 2012-2016 Project Delphinium - All Rights Reserved
 *
 * This file is subject to the terms and conditions defined in
 * file 'https://github.com/ProjectDelphinium/delphinium/blob/master/EULA',
 * which is part of this source code package.
 *
 * NOTICE:  All information contained herein is, and remains the property of Project Delphinium. The intellectual and technical concepts contained
 * herein are proprietary to Project Delphinium and may be covered by U.S. and Foreign Patents, patents in process, and are protected by trade secret or copyright law.
 * Dissemination of this information or reproduction of this material is strictly forbidden unless prior written permission is obtained
 * from Project Delphinium.
 *
 * THE RECEIPT OR POSSESSION OF THIS SOURCE CODE AND/OR RELATED INFORMATION DOES NOT CONVEY OR IMPLY ANY RIGHTS
 * TO REPRODUCE, DISCLOSE OR DISTRIBUTE ITS CONTENTS, OR TO MANUFACTURE, USE, OR SELL ANYTHING THAT IT  MAY DESCRIBE, IN WHOLE OR IN PART.
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Non-commercial use only, you may not charge money for the software
 * You can modify personal copy of source-code but cannot distribute modifications
 * You may not distribute any version of this software, modified or otherwise
 */

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
            if($scope.selectedModulePrereqs[x]['id']!=="0")
            {
                prereqs.push($scope.selectedModulePrereqs[x]['id']);
            }
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






