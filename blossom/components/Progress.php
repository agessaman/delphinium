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

namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;

class Progress extends ComponentBase
{
    public $roots;
    public function componentDetails()
    {
        return [
            'name'        => 'Progress',
            'description' => 'Shows students progress toward finishing the course'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
    public function onRun()
    {
        try{
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/progress.js");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
            $this->addCss("/plugins/delphinium/blossom/assets/css/progress.css");

            $this->roots = new Roots();
            try {
                $res = $this->roots->getAnalyticsStudentAssignmentData();
            } catch (\GuzzleHttp\Exception\ClientException $e) {
                echo "In order for the 'Progress' app to run properly you must be a student or you must go in 'Student View'";
                return;
            }
            //var_dump($res);
            $possable = 0;
            $completed = 0;
            foreach ($res as $assignment) {
                foreach ($assignment as $key => $submission) {
                    if($key=="submission"){
                        $completed++;
                    }
                }
                $possable++;
            }
            $progress = $completed / $possable;
            $this->page['progress'] = $progress;
        }
        catch (\GuzzleHttp\Exception\ClientException $e) {
            return;
        }
        catch(Delphinium\Roots\Exceptions\NonLtiException $e)
        {
            if($e->getCode()==584)
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
        }
        catch(\Exception $e)
        {
            if($e->getMessage()=='Invalid LMS')
            {
                return \Response::make($this->controller->run('nonlti'), 500);
            }
            return \Response::make($this->controller->run('error'), 500);
        }
    }

}