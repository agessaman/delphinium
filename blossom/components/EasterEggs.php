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
use Delphinium\Blossom\Models\EasterEggs as EasterEggsModel;
use Delphinium\Blossom\Components\Experience as ExperienceComponent;
use Delphinium\Roots\Roots;
use Flash;
use Redirect;

class EasterEggs extends ComponentBase
{
    public $courseId;
    public $config;
    public $eggsInstanceId;

    public function componentDetails()
    {
        return [
            'name'        => 'EasterEggs',
            'description' => 'Find the easter eggs!'
        ];
    }

    public function defineProperties()
    {
        return [
            'instance'   => [
                'title'             => 'EasterEggs Configuration',
                'description'       => 'Select an instance',
                'type'              => 'dropdown',
                'default'           => 0
            ]
        ];
    }

    public function onRun()
    {
        try
        {
            $this->addCss('/modules/system/assets/ui/storm.css', 'core');
            $this->addJs('/modules/system/assets/ui/storm-min.js', 'core');
            $this->addCss('/modules/system/assets/ui/storm.less', 'core');
            $this->addCss("https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css");
            $this->addJs("/plugins/delphinium/blossom/assets/javascript/bootstrap.min.js");
            $this->addCss("/plugins/delphinium/blossom/assets/css/eastereggs.css");
            

            //if no instance exists of this component, create a new one.
            $config = $this->firstOrNewCourseInstance();
            $this->eggsInstanceId = $config->id;
            $this->page['instance_id'] =  $config->id;
            
            // use the record in the component and frontend form 
            $this->page['config'] = json_encode($config);
            
            
            /** get roles, a comma delimited string
             * check if Student
             * if not then set to Instructor. disregard any other roles?
             * role is used to determine functions and display options
             */

            $roleStr = $_SESSION['roles'];
            
            if(stristr($roleStr, 'Learner')) {
                $roleStr = 'Learner';
            } else { 
                $roleStr = 'Instructor';
            }
            $this->page['role'] = $roleStr;// only one or the other
            
            $path = \Config::get("app.url");
            $this->page['path'] = $path;

            $menu = $config->menu;
            $this->page['menu'] = $menu;

            $exComp = new ExperienceComponent();
            $points = $exComp->getUserPoints();
            $this->page['current_grade'] = $points;
            
            // include your css note: bootstrap.min.css is part of minimal layout
            
            
            // include the backend form with instructions for instructor.htm
            if(stristr($roleStr, 'Instructor'))
            {
                //https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
                // Build a back-end form with the context of 'frontend'
                $formController = new \Delphinium\Blossom\Controllers\EasterEggs();
                $formController->create('frontend');
                
                //this is the primary key of the record you want to update
                $this->page['eggsRecordId'] = $config->id;
                // Append the formController to the page
                $this->page['eggsForm'] = $formController;
                
                // Append Instructions page
                $instructions = $formController->makePartial('eggsinstructions');
                $this->page['eggsinstructions'] = $instructions;

                //$this->addJs("/plugins/delphinium/blossom/assets/javascript/popover.js");

                //$this->addCss('/modules/system/assets/ui/storm.css', 'core');
                //$this->addJs('/modules/system/assets/ui/storm-min.js', 'core');
                //$this->addCss('/modules/system/assets/ui/storm.less', 'core');

                
                //code specific to instructor.htm goes here
            }
            
            if(stristr($roleStr, 'Learner'))
            {
                //code specific to the student.htm goes here
            }
            // code used by both goes here
            
        // Error handling requires nonlti.htm
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

    public function getInstanceOptions()
    {
        $instances = EasterEggsModel::all();
        $array_dropdown = ['0'=>'- select Instance - '];

        foreach ($instances as $instance)
        {
            $array_dropdown[$instance->id] = $instance->name;
        }
        return $array_dropdown;
    }

    private function firstOrNewCourseInstance($copyName=null)
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $courseId = $_SESSION['courseID'];
        $this->courseId = $courseId;
        $courseInstance = null;

        //if they have selected a backend instance, that will take precedence over creating a dynamic instance based on the component alias
        if(($this->property('eggs'))>0)
        {
            $courseInstance =EasterEggsModel::firstOrNew(array('id' => $this->property('eggs')));
        }
        else
        {//didn't select a backend instance. Create the component based on the copy name, or the alias name if the copy name was not provided
            if(is_null($copyName))
            {
                $copyName =$this->alias . "_".$courseId;
            }
            $courseInstance =EasterEggsModel::firstOrNew(array('name'=>$copyName));
            $courseInstance->course_id = $courseId;
            $courseInstance->name = $copyName;
        }

        if(is_null($courseInstance->menu)){$courseInstance->menu = 0;}
        $courseInstance->save();
        return $courseInstance;
    }

    public function onSave()
    {
        $data = post('EasterEggs');
        //var_dump($data);
        $config = $this->firstOrNewCourseInstance($data['name']);//get the instance
        $config->name = $data['name'];
        $config->menu = $data['menu'];
        //$config->course_id = $data['course_id'];
        $config->harlem_shake = $data['harlem_shake'];
        $config->ripples = $data['ripples'];
        $config->asteroids = $data['asteroids'];
        $config->katamari = $data['katamari'];
        $config->bombs = $data['bombs'];
        $config->ponies = $data['ponies'];
        $config->my_little_pony = $data['my_little_pony'];
        $config->snow = $data['snow'];
        $config->raptor = $data['raptor'];
        $config->fireworks = $data['fireworks'];
        $config->fireworks_string = $data['fireworks_string'];
        $config->save();
        return json_encode($config);
    }
}