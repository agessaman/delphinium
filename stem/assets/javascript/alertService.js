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

    angular.module('myApp')
    .factory('alertService', alertService);

    function alertService() {
        var service = {
            add: add,
            closeAlert: closeAlert,
            closeAlertIdx: closeAlertIdx,
            clear: clear,
            get: get
        },
        alerts = [];

        return service;

        function add(type, msg) {
            return alerts.push({
                type: type,
                msg: msg,
                close: function() {
                    return closeAlert(this);
                }
            });
        }

        function closeAlert(alert) {
            return closeAlertIdx(alerts.indexOf(alert));
        }

        function closeAlertIdx(index) {
            return alerts.splice(index, 1);
        }

        function clear(){
            alerts = [];
        }

        function get() {
            return alerts;
        }
    }
})();