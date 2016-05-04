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

'use strict';
var ModalInstanceCtrl = function ($scope, $modalInstance, $location, itemIn) {

    $scope.item = itemIn;
    $scope.ok = function () {
        $scope.jobData.executeNow = false;
        $modalInstance.close($scope.item);
    };

    $scope.cancel = function () {

        $modalInstance.dismiss('cancel');

    };
    $scope.showTagsFunc = function (scope) {
        this.showTags = !this.showTags;
        var itemId = (scope.item.module_item_id);
        //show/hide div
        var node = document.getElementById("div" + itemId);
        var currClassName = node.className;

        var nodes = document.getElementsByClassName("tagNode");
        for (var i = 0; i <= nodes.length - 1; i++) {
            nodes[i].className = "hidden tagNode";
        }

        if (currClassName === "visible tagNode") {
            node.className = "hidden tagNode";
        }
        else {
            node.className = "visible tagNode";
            var arr = scope.item.content[0].tags;
            if (arr.length > 0) {
                $scope.tags = arr.split(", ");
            }
            else {
                $scope.tags = [];
            }
            var diff = findDifference(avTags, $scope.tags);
            $scope.possibleTags = diff;
        }


    };


    $scope.delete = function(scope){
        //alert($scope.item[4].name);

        alert('Are you sure you want to permanently delete this module?');

        $modalInstance.dismiss('delete');
    }
};


var ModalCtrl = function ($scope, $modal, $log ) {

    $scope.open = function (item) {


        $scope.item = item;
        var modalInstance = $modal.open({
            templateUrl: "modalTemp.html",
            controller: "JobModalInstanceCtrl",
            resolve: {
                itemIn: function() {
                    return item;
                }
            }

        });
        modalInstance.result.then(function (){



        }, function (input) {
            if (input === 'delete'){
                $scope.remove(item);
            }
            $log.info('Modal dismissed at: ' + new Date());
        });
    };
};




