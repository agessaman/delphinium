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
use \DateTime;
use \DateInterval;

class Timer extends ComponentBase
{
    public $roots;

    public function componentDetails()
    {
        return [
            'name'        => 'Timer',
            'description' => 'Counts down till end of the course'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        try{


            if(!isset($_SESSION))
            {
                session_start();
            }
            $courseId = $_SESSION['courseID'];
            if(!isset($_SESSION['userToken']))//app hasn't been approved by admin
            {
                return;
            }
            $this->roots = new Roots();

            try {
                $enrollments = $this->roots->getUserEnrollments();
                foreach($enrollments as $course)
                {
                    if ($course->course_id==$courseId)
                    {
                        $res = $course;
                        break;
                    }
                }

                $end = new DateTime($res->created_at);
                $end->add(new DateInterval('P60D'));

                $this->page['start'] = $res->created_at;
                $this->page['end'] = $end->format('c');


                $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
                $this->addJs("/plugins/delphinium/blossom/assets/javascript/timer.js");
                $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
                $this->addCss("/plugins/delphinium/blossom/assets/css/timer.css");

            }
            catch (\GuzzleHttp\Exception\ClientException $e)
            {
                $end = new DateTime("now");
                $this->page['start'] = $end->format('c');
                $this->page['end'] = $end->format('c');
                echo "An error has occurred. An invalid user id was provided. You must be a student to use this app, or go into 'Student View'. "
                    . "Also, make sure that an administrator has approved this application (only administrators have permissions "
                    . "to see user enrollments)";
                return;
            }
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