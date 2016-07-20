<?php
/**
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

namespace Delphinium\Dev\Components;

use Delphinium\Dev\Models\Configuration;
use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;

class Dev extends ComponentBase
{
// 	public $chartName;

    public function componentDetails()
    {
        return [
            'name'        => 'Dev Component',
            'description' => 'If added to a page it will enable dev mode for Delphinium'
        ];
    }

    public function onRun()
    {
        $config = Configuration::find($this->property('devConfig'));

        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['userID'] = $config->User_id;
        $_SESSION['userToken'] = \Crypt::encrypt($config->Token);
        $_SESSION['courseID'] = $config->Course_id;
        $_SESSION['domain'] = $config->Domain;
        $_SESSION['lms'] = $config->Lms;

        //get the roles
        $roleStr = $config->Roles;
        $_SESSION['roles'] = $roleStr;

        $_SESSION['timezone'] = new \DateTimeZone($config->Timezone);
        //get the timezone
        $roots = new Roots();
        $course = $roots->getCourse();
        $account_id = $course->account_id;

        try
        {
            $account = $roots->getAccount($account_id);

            $_SESSION['timezone'] = new \DateTimeZone($account->default_time_zone);
        } catch (\GuzzleHttp\Exception\ClientException $e) {

        }
    }

    public function defineProperties()
    {
        return [
            'devConfig' => [
                'title'             => 'Dev Configuration',
                'description'       => 'Select the development configuration',
                'type'              => 'dropdown',
            ]
        ];
    }

    public function getDevConfigOptions()
    {
        $instances = Configuration::where("Enabled","=","1")->get();

        $array_dropdown = ['0'=>'- select dev Config - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->Configuration_name;
        }

        return $array_dropdown;
    }
}