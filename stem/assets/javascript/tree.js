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

(function () {
    'use strict';
    angular.module('treeApp', ['ui.tree', 'xeditable', 'ui.bootstrap']).run(function (editableOptions) {
        editableOptions.theme = 'bs2';
    })
            .controller('treeCtrl', function ($scope, $http, $interval, $timeout, $q) {

                $scope.data = moduleData;
                $scope.contentClass = "hidden";
                $scope.currentModuleItemId = 0;
                $scope.contentId = 0;
                $scope.isExpanded = false;
                $scope.showTags = false;
                $scope.loading = false;
                
                $scope.alerts = [];
                /*
                 * ***********************  Functions  ***********************
                 */
                $scope.addAlert = function(type, msg) {
                    $scope.alerts.push({msg: msg, type: type});
                };

                  $scope.closeAlert = function(index) {
                    $scope.alerts.splice(index, 1);
                  };
                $scope.showContent = function (item) {
                    $scope.isExpanded = !$scope.isExpanded;

                    var node = document.getElementById("div" + item.module_id);
                    var currClassName = node.className;

                    var nodes = document.getElementsByClassName("node");
                    for (var i = 0; i <= nodes.length - 1; i++) {
                        nodes[i].className = "hidden node";
                    }

                    if (currClassName === "visible node") {
                        node.className = "hidden node";
                    }
                    else {
                        node.className = "visible node";
                    }
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

                $scope.treeOptions = {
                    accept: function (sourceNodeScope, destNodesScope) {
                        //if the module is unpublished it can't be dropped to the top position
                        if ((!destNodesScope.$nodeScope) && (sourceNodeScope.$parentNodeScope) && (sourceNodeScope.$modelValue.published === "0")) {
                            //show flash message explainig what is going on
                            $scope.addAlert("danger","This item cannot be dragged to the top because it is unpublished");
                            $timeout(function(){
                                $scope.alerts.splice($scope.alerts.indexOf(alert), 1);
                            }, 4000);
//                        $(selector for your message).slideDown(function() {
//                            setTimeout(function() {
//                                $(selector for your message).slideUp();
//                            }, 5000);
//                        });

                            return false;
                        }
                        else {
                            return true;
                        }
                    },
                    dropped: function (event) {
                        //if the item was moved to the first position, this item will now become the parent of all other items
                        if ((!event.dest.nodesScope.$nodeScope) && (event.source.nodeScope.$parentNodeScope)) {
                            var parent = event.dest.nodesScope.$modelValue[0];
                            var allOtherItems = event.dest.nodesScope.$modelValue[1];
                            console.log('parent:',parent);
                            console.log('otherItems:',allOtherItems);
                            $http.post('moveItemToTop', {
                                parent: JSON.stringify(parent),
                                modulesArray: JSON.stringify(allOtherItems)
                            })
                                    .success(function (data, status) {
                                        console.log('success:',data);
                                        $scope.data = data;
                                        $scope.saveOrder($scope);
                                    })
                                    .error(function (data) {
                                    });
                        }
                        else if (event.source.nodeScope.$parentNodeScope)//if nodeScope.$parentNodeScope is undefined, it means the top element is being dragged,
                                //in which case we don't want to save the order
                                {
                                    //just save the order
                                    $scope.saveOrder($scope);
                                }
                    }
                };

                $scope.remove = function (scope) {
                    scope.remove();
                };

                $scope.toggle = function (scope) {
                    scope.toggle();
                };

//this function below (or something similar) will be used by Tara when adding new functionality to the manager

                $scope.newSubItem = function (scope) {
                    var nodeData = scope.$modelValue;
                    nodeData.nodes.push({
                        id: nodeData.id * 10 + nodeData.nodes.length,
                        title: nodeData.title + '.' + (nodeData.nodes.length + 1),
                        nodes: []
                    });
                };

                $scope.collapseAll = function () {
                    $scope.$broadcast('collapseAll');
                };

                $scope.expandAll = function () {
                    $scope.$broadcast('expandAll');
                };

                $scope.saveOrder = function (scope) {
//        console.log(JSON.stringify($scope.data));
                    $http.post('saveModules', {
                        courseId: courseId,
                        modulesArray: JSON.stringify($scope.data), updateLms: false})

                            .success(function (data, status) {
                                console.log("saved Data",data);
                                //console.log(data);
                            })
                            .error(function (data) {
                            });
                };

                $scope.addTags = function (scope) {
                    var content_id = scope.item.content_id;
                    var currTags = getCurrentTags(scope.item);
                    var newTags = scope.tagValue.split(",");
                    var tagArr = currTags.concat(newTags);

                    for (var i = 0; i <= tagArr.length - 1; i++) {
                        var a = tagArr[i].trim();
                        a = capitalizeFirstLetter(a);
                        tagArr[i] = a;

                    }
                    $http.post('addTags', {
                        contentId: content_id,
                        tags: JSON.stringify(tagArr)
                    }).
                            success(function (data) {
                                $scope.tags = data.split(", ");
                                scope.item.content[0].tags = data;

                                $scope.updateAvailableTags();
                            });
                    this.tagValue = "";
                };

                $scope.addInnerTag = function (scope, tag) {
                    var arr = [tag];
                    var currTags = getCurrentTags(scope.$parent.item);
                    var tagArr = currTags.concat(arr);
                    var content_id = scope.$parent.item.content_id;

                    for (var i = 0; i <= tagArr.length - 1; i++) {
                        var a = tagArr[i].trim();
                        a = capitalizeFirstLetter(a);
                        tagArr[i] = a;
                    }

                    $http.post('addTags', {
                        contentId: content_id,
                        tags: JSON.stringify(tagArr)
                    }).
                            success(function (data) {
                                $scope.tags = data.split(", ");
                                scope.$parent.item.content[0].tags = data;
                                $scope.updateAvailableTags();
                            });
                    $scope.tagValue = "";
                }

                $scope.deleteTag = function (scope, tag) {
                    var currTags = getCurrentTags(scope.$parent.item);
                    var content_id = scope.$parent.item.content_id;
                    var trimmed = tag.trim();
                    var diff = findDifference(currTags, [trimmed]);

                    $http.post('deleteTag', {contentId: content_id, tags: JSON.stringify(diff)})
                            .success(function (data) {
                                var t = data.split(", ");
                                if (data === "") {
                                    scope.$parent.item.content[0].tags = data;
                                    $scope.tags = [];
                                }
                                else {
                                    $scope.tags = t;
                                    scope.$parent.item.content[0].tags = data;
                                }

                                //need to also update this item's current tags
                                $scope.updateAvailableTags();
                            })
                            .error(function (data) {
                            });
                };

                $scope.updateAvailableTags = function () {
                    $http.get("getAvailableTags", {
                        params: {
                            courseId: courseId
                        }
                    })
                    .success(function (data, status) {
                        if (data.length > 0) {
                            avTags = data.split(", ");
                        }

                        var diff = findDifference(avTags, $scope.tags);
                        $scope.possibleTags = diff;
                    });
                };

                $scope.switchPublishedState = function(item)
                {
                    var is_module = (item.module_item_id !== undefined) ? false : true;
                    var publishedState = (item.published ==='1') ? '0' : '1';
                    
                    if(is_module)
                    {
                        $http.post('toggleModulePublishedState', {
                            module_id: item.module_id,
                            published: parseInt(publishedState)
                        }).
                        success(function (data) {
                            item.published = publishedState;
                        });
                    }
                    else
                    {
                        $http.post('toggleModuleItemPublishedState', {
                            module_id: item.module_id,
                            module_item_id: item.module_item_id,
                            published: parseInt(publishedState)
                        }).
                        success(function (data) {
                            item.published = publishedState;
                        });
                    }
                };



                $scope.reloadApp = function ()
                {
                    $scope.loading = true;
                    $http.get("getFreshData")
                            .success(function (data, status) {
                                if (status === 200)
                                {
                                    $scope.data = data;
                                    $scope.loading = false;
                                }

                            });
                };

                $scope.postOrderToLms = function ()
                {
                    var deferred = $q.defer();
                    $http.post('saveModules', {courseId: courseId,
                        modulesArray: JSON.stringify($scope.data), updateLms: true})
                            .success(function (data, status) {
                                $timeout(function(){
                                    $scope.postOrderToLms();
                                },60000);
                            })
                            .error(function (data) {
                            });
                    return deferred.promise;
                };

                $scope.initManager = function()
                {
                    $scope.saveOrder();//save the new order right away
                    
                    $http.get("getModuleItemTypes")
                    .success(function (data, status) {

                            $scope.moduleItemTypes = data;
                    });
                    
//                    $timeout(function(){
//                        $scope.postOrderToLms();
//                    },30000);
                };
                
                $scope.initManager();
                
            });

})();


/*
 * Additional functions
 */

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);

}

function findDifference(a, b) {
    var seen = [], diff = [];
    for (var i = 0; i < b.length; i++)
        seen[b[i].trim()] = true;
    for (var i = 0; i < a.length; i++)
        if (!seen[a[i].trim()])
            diff.push(a[i].trim());
    return diff;
}

function getCurrentTags(moduleItem) {
    tagStr = moduleItem.content[0].tags;
    if (tagStr.length > 0) {
        return tagStr.split(", ");
    }
    else {
        return [];
    }
}



