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

class EasterEggs extends ComponentBase
{

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
            if (!isset($_SESSION)) { session_start(); }
            $courseID = $_SESSION['courseID'];
            $name = $this->alias .'_'. $_SESSION['courseID'];
            
            // if instance has been set
            if( $this->property('instance') )
            {
                //use the instance set in CMS dropdown
                $config = EasterEggsModel::find($this->property('instance'));

            } else {
                // look for instances created for this course
                $instances = EasterEggsModel::where('name','=', $name)->get();
                
                if(count($instances) === 0) { 
                    // no record found so create a new dynamic instance
                    $config = new EasterEggsModel;// db record
                    $config->name = $name;
                    $config->menu = 0;
                    $config->harlem_shake = 0;
                    $config->ripples = 0;
                    $config->asteroids = 0;
                    $config->katamari = 0;
                    $config->bombs = 0;
                    $config->ponies = 0;
                    $config->my_little_pony = 0;
                    $config->snow = 0;
                    // add your fields
                    //$config->size = '20%';
                    $config->save();// save the new record
                } else {
                    //use the first record matching course
                    $config = $instances[0];
                }
            }
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
            $this->addCss("/plugins/delphinium/blossom/assets/css/eastereggs.css");
            $this->addCss("https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css");

            
            // include the backend form with instructions for instructor.htm
            if(stristr($roleStr, 'Instructor'))
            {
                //https://medium.com/@matissjanis/octobercms-using-backend-forms-in-frontend-component-fe6c86f9296b#.ge50nlmtc
                // Build a back-end form with the context of 'frontend'
                $formController = new \Delphinium\Blossom\Controllers\EasterEggs();
                $formController->create('frontend');
                
                //this is the primary key of the record you want to update
                $this->page['recordId'] = $config->id;
                // Append the formController to the page
                $this->page['form'] = $formController;
                
                // Append Instructions page
                $instructions = $formController->makePartial('instructions');
                $this->page['instructions'] = $instructions;
                
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

    
    public function onUpdate()
    {
        $data = post('EasterEggs');//component name
        $did = intval($data['id']);// convert string to integer
        $config = EasterEggsModel::find($did);// retrieve existing record
        $config->name = $data['name'];// change to new data
        $config->menu = $data['menu'];
        $config->harlem_shake = $data['harlem_shake'];
        $config->ripples = $data['ripples'];
        $config->asteroids = $data['asteroids'];
        $config->katamari = $data['katamari'];
        $config->bombs = $data['bombs'];
        $config->ponies = $data['ponies'];
        $config->my_little_pony = $data['my_little_pony'];
        $config->snow = $data['snow'];
        $config->save();// update original record 
        return json_encode($config);// back to instructor view
    }
}