<?php

namespace Delphinium\Roots\Components;

use Delphinium\Roots\Models\Developer as LtiConfigurations;
use Delphinium\Roots\Models\User;
use Delphinium\Roots\Models\UserCourse;
use Cms\Classes\ComponentBase;
use Cms\Classes\Theme;
use Delphinium\Roots\Classes\Blti;
use Delphinium\Roots\Roots;
use Delphinium\Roots\DB\DbHelper;
use Config;
use Carbon\Carbon;
use Delphinium\Roots\Exceptions\NonLtiException;

class LtiConfiguration extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => 'LTI Configuration Component',
            'description' => 'Handles the LTI Configuration required for communicating with Canvas'
        ];
    }

//    public function onRun() {
//        try
//        {
//        $this->doBltiHandshake();
//        }
//        catch(\Delphinium\Roots\Exceptions\InvalidRequestException $e)
//        {
//            return \Response::make($this->controller->run('error'), 500);
//        }
//        catch(NonLtiException $e)
//        {
//            if($e->getCode()==584)
//            {
//                return \Response::make($this->controller->run('nonlti'), 500);
//            }
//            else{
//                echo json_encode($e->getMessage());return;
//            }
//        }
//        catch (\GuzzleHttp\Exception\ClientException $e) {
//            return;
//        }
//        catch(\Exception $e)
//        {
//            if($e->getMessage()=='Invalid LMS')
//            {
//                return \Response::make($this->controller->run('nonlti'), 500);
//            }
//            return \Response::make($this->controller->run('error'), 500);
//        }
//  }
    public function onRun()
    {
        $this->checkLTIMessageType();
    }


    function checkLTIMessageType()
    {
        if (isset($_POST['lti_message_type'])) {
            $this->page['messageType'] = $_POST['lti_message_type'];
            switch ($this->page['messageType']) {
                case 'ContentItemSelectionRequest':
                    $this->page['return_url'] = $_POST["content_item_return_url"];
                    break;
                case 'basic-lti-launch-request':
                default:
                   // try {
                        $this->doBltiHandshake();
                   // } catch (\Delphinium\Roots\Exceptions\InvalidRequestException $e) {
                   //     return \Response::make($this->controller->run('error'), 500);
                   // } catch (NonLtiException $e) {
                   //     if ($e->getCode() == 584) {
                   //         return \Response::make($this->controller->run('nonlti'), 500);
                   //     } else {
                   //         echo json_encode($e->getMessage());
                   //         return;
                   //     }
                   // } catch (\GuzzleHttp\Exception\ClientException $e) {
                   //     return;
                   // } catch (\Exception $e) {
                   //     if ($e->getMessage() == 'Invalid LMS') {
                   //         return \Response::make($this->controller->run('nonlti'), 500);
                   //     }
                   //     return \Response::make($this->controller->run('error'), 500);
                   // }
            }
        } else {
            $this->returnXML();
        }
    }

    public function defineProperties()
    {
        return [
            'ltiInstance' => [
                'title' => 'LTI Instance',
                'description' => 'Select the LTI configuration instance to use for connecting to Canvas',
                'type' => 'dropdown',
            ],
            'approver' => [
                'title' => 'Approver',
                'description' => 'The approver must have the right permissions to access the data needed for this component',
                'type' => 'dropdown',
                'default' => 'Instructor',
            ],
            'type' =>[
                'title'=>'Type',
                'description' => 'Whether this LTI should appear in a left hand menu item or in the editor toolbar.',
                'type' => 'dropdown',
                'default' => 'Navigation',
            ],
            'title' =>[
                'title'=>'Link Title',
                'description' => 'Enter the name of the link as you would like it to show in the LMS',
                'type' => 'string',
                'required'=>true,
                'validationPattern' => '^[a-zA-Z0-9\s]+$',
                'validationMessage' => 'Link Title is required'
            ],
            'visibility' =>[
                'title'=>'Visibility',
                'description' => 'Select who will be able to see this content. Default is public.',
                'type' => 'dropdown',
                'default' => 'Public',
            ],
            'width' =>[
                'title'=>'Width (Editor type only)',
                'description' => 'This setting applies to the \'Editor\' type only. Enter the width of the iframe that will display this content in the text editor',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Width must be an integer',
                'default' => '600',
            ],
            'height' =>[
                'title'=>'Height (Editor type only)',
                'description' => 'This setting applies to the \'Editor\' type only.  Enter the height of the iframe that will display this content in the text editor',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Height must be an integer',
                'default' => '800',
            ]
        ];
    }

    public function getLtiInstanceOptions()
    {
        $instances = LtiConfigurations::all();
        $array_dropdown = ['0' => '- select an LTI configuration - '];

        foreach ($instances as $instance) {
            $array_dropdown[$instance->id] = $instance->Name;
        }

        return $array_dropdown;
    }

    public function getApproverOptions()
    {
        $arr = array(
            "0" => "Instructor",
            "1" => "Administrator"
        );
        return $arr;
    }

    public function getTypeOptions()
    {
        return array(0=>'Navigation',1=>'Editor');
    }

    public function getVisibilityOptions()
    {
        return array(0=>'Public',1=>'Members',2=>'Admin');
    }
    public function doBltiHandshake()
    {
        //first obtain the details of the LTI configuration they chose
        $dbHelper = new DbHelper();
        $instanceFromDB = LtiConfigurations::find($this->property('ltiInstance'));
        $approver = $this->property('approver');
        $arr = $this->getApproverOptions();
        $approverRole = $arr[$approver];

        if (!isset($_SESSION)) {
            session_start();
        }

        $_SESSION['baseUrl'] = Config::get('app.url', 'backend');
        $_SESSION['courseID'] = \Input::get('custom_canvas_course_id');
        $_SESSION['userID'] = \Input::get('custom_canvas_user_id');
        $_SESSION['domain'] = \Input::get('custom_canvas_api_domain');
      // echo json_encode($_POST);return ;

        //get the roles

        $possibleRoles = array('Learner','Instructor','TeachingAssistant','Administrator');
        $roleStr = \Input::get('roles');
        $userRolesArr = array();
        foreach($possibleRoles as $role)
        {
            if(stristr($roleStr, $role))
            {
                $userRolesArr[] = $role;
            }
        }

        $userRolesStr = implode(", ",$userRolesArr);
        if(count($userRolesArr)>1)
        {
            if(stristr($userRolesStr, $approverRole))
            {
                $_SESSION['roles'] =$approverRole;//if the user has more than one role, default to the approver role
            }
            else
            {
                $_SESSION['roles'] =$userRolesArr[0];//if they have more than one role, but they don't include the approver role, default to
                //the first role
            }
        }
        else
        {
            $_SESSION['roles'] =$userRolesArr[0];
        }

        //TODO: make sure this parameter below works with all other LMSs
        $_SESSION['lms'] = \Input::get('tool_consumer_info_product_family_code');

        //check to see if user is an Instructor
        $rolesStr = \Input::get('roles');
        $secret = $instanceFromDB['SharedSecret'];
        $clientId = $instanceFromDB['DeveloperId'];

        //Check to see if the lti handshake passes
        $context = new Blti($secret, false, false);

        if ($context->valid) { // query DB to see if user has token, if yes, go to LTI.
<<<<<<< HEAD
=======

>>>>>>> 458e203e636a22db079d0e2b12c60aa91cba5e3b
            //parameters needed to request for the token
            //TODO: take this redirectUri out into some parameter somewhere...
            $baseUrlWithSlash = rtrim($_SESSION['baseUrl'], '/') . '/';
            $domainWithSlash = rtrim($_SESSION['domain'], '/') . '/';


            //check to see if user is an Instructor
            $rolesStr = $_SESSION['roles'];

            $roleId = $dbHelper->getRole($rolesStr)->id;
            $lti = $this->property('ltiInstance');

            $data = array('lti'=>intval($lti),'role'=>intval($roleId));
            $query = http_build_query($data);

            $redirectUri = urlencode("{$baseUrlWithSlash}saveUserInfo?$query");
            $url = "https://{$domainWithSlash}login/oauth2/auth?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";

            $userCheck = $dbHelper->getCourseApprover($courseId);
<<<<<<< HEAD

=======
>>>>>>> 458e203e636a22db079d0e2b12c60aa91cba5e3b
            if (!$userCheck) { //if no user is found, redirect to canvas permission page
                if (stristr($rolesStr, $approverRole)) {
                    //As per my discussion with Jared, we will use the instructor's token only. This is the token that will be stored in the DB
                    //and the one that will be used to make all requests. We will NOT store student's tokens.
                    //TODO: take this redirectUri out into some parameter somewhere...

                    $baseUrlWithSlash = rtrim($_SESSION['baseUrl'], '/') . '/';
                    $domainWithSlash = rtrim($_SESSION['domain'], '/') . '/';

                    $redirectUri = "{$baseUrlWithSlash}saveUserInfo?lti={$this->property('ltiInstance')}";
                    $url = "https://{$domainWithSlash}login/oauth2/auth?client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}";
                    $this->redirect($url);
                } else {
                    echo("A(n) {$approverRole} must authorize this course. Please contact your instructor.");
                    return;
                }
            } else {
                //set the professor's token
                $courseId = $_SESSION['courseID'];
                $_SESSION['userToken'] = $userCheck->encrypted_token;
                //get the timezone
                $roots = new Roots();

                try {
                    $course = $roots->getCourse();
                if(count($course)<1)
                    {
                        throw new \Exception("Invalid access token", 401);
                    } 
                } catch(\Exception $e) {
                    if ($e->getCode() == 401) {//unauthorized, meaning the token we have in the DB has been deleted from Canvas. We must request a new token
                        $dbHelper->deleteInvalidApproverToken($courseId);

                        //launch the approval process again, try three times at most
                        if (isset($_COOKIE['token_attempts'])) 
                        {
                            $attempts = $_COOKIE['token_attempts'] + 1;
                            setcookie("token_attempts", $attempts, time() + (300), "/"); //5 minutes
                        } else {
                            setcookie("token_attempts", 1, time() + (300), "/"); //5 minutes
                        }
                        if ((isset($_COOKIE['token_attempts']))||($_COOKIE['token_attempts'] > 3)) {
                            echo "Unable to obtain access to your Canvas account. Reached the max number of attempts. Please verify your configuration and try again in 5 minutes.";
                            return;
                        } else {
                            $this->onRun();//the cookie is done to prevent infinite loops
                        }
                    }
                }

                $course = $roots->getCourse();
	
                $account_id = $course->account_id;
                $account = $roots->getAccount($account_id);
                $_SESSION['timezone'] = new \DateTimeZone($account->default_time_zone);
                //to maintain the users table synchronized with Canvas, everytime a student comes in we'll check to make sure they're in the DB.
                //If they're not, we will pull all the students from Canvas and refresh our users table.
                $dbHelper = new DbHelper();
                $user = $dbHelper->getUserInCourse($courseId, $_SESSION['userID']);
                if (is_null($user)) {//get all students from Canvas
                    $roots = new Roots();
                    $users = $roots->getStudentsInCourse();

                }

                //Also, every so often (every 12 hrs?) we will check to make sure that students who have dropped the class are deleted from the users_course table
                //Failing to do so will make it so that when we request their submissions along with other students' submissions, the entire
                // call returns with an Unauthorized error message
                $approver = $dbHelper->getCourseApprover($courseId);
                $now = Carbon::now();
                $updatedDate = $approver->updated_at;

                $diff = $updatedDate->diffInHours($now, false);
                if ($diff > 24) {
                    $allStudentsDb = $dbHelper->getUsersInCourseWithRole($_SESSION['courseID'], 'Learner');
                    $allStudentsFromCanvas = $roots->getStudentsInCourse();
                    foreach ($allStudentsDb as $dbStudent) {
                        $filteredItems = array_values(array_filter($allStudentsFromCanvas, function ($elem) use ($dbStudent) {
                            return intval($elem->user_id) === intval($dbStudent->user_id);
                        }));

                        if (count($filteredItems) < 1)//meaning they are in our DB but they are not in Canvas anymore
                        {
                            $dbHelper->deleteUserFromRole($courseId, $dbStudent->user_id, 'Learner');
                        }
                    }

                    //update the approver
                    $approver->updated_at = $now;
                    $approver->save();
                }
            }
        } else {
            echo('There is a problem. Please notify your instructor');
        }
    }

    function redirect($url)
    {
        echo '<script type="text/javascript">';
        echo 'window.location.href="' . $url . '";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
        echo '</noscript>';
        exit;
    }

    function returnXML()
    {	
        $baseUrlWithoutSlash = rtrim(\Config::get('app.url'), '/');
        $url = $baseUrlWithoutSlash . $this->page->url;
        $typeOpts = $this->getTypeOptions();
        $type= $typeOpts[$this->property('type')];

        $linkTitle = $this->property('title');
        $width = $this->property('width');
        $height = $this->property('height');

        $visibilityOpts = $this->getVisibilityOptions();
        $visibility= $visibilityOpts[$this->property('visibility')];
        $domain = \Config::get('app.url');

        $theme = Theme::getActiveTheme();
        $path = $theme->getDirName();
        $favicon = \URL::to('themes/'.$path.'/assets/images/favicon.png');
        $desc = is_null($this->page->description)?'':$this->page->description;

        $widthXML='';
        $heightXML = '';
        $typeXML='';
        $varsXml='';
        $string='';
        if(!is_null($width))
        {
            $widthXML = "<lticm:property name='selection_width'>$width</lticm:property>";
        }

        if(!is_null($height))
        {
            $heightXML = "<lticm:property name='selection_height'>$height</lticm:property>";
        }


        if ($type == 'Navigation'){
            $typeXML = <<<XML
			<lticm:options name='course_navigation'>
                <lticm:property name='visibility'>$visibility</lticm:property>
                <lticm:property name='default'>enabled</lticm:property>
                <lticm:property name='url'>$url</lticm:property>
                <lticm:property name='text'>$linkTitle</lticm:property>
                <lticm:property name='enabled'>true</lticm:property>
          </lticm:options>
XML;
        }elseif ($type == 'Editor'){
            $typeXML = <<<XML
			<lticm:options name='editor_button'>
				<lticm:property name='icon_url'>$favicon</lticm:property>
				$widthXML
				$heightXML
				<lticm:property name='url'>$url</lticm:property>
                <lticm:property name='text'>$linkTitle</lticm:property>
                <lticm:property name='enabled'>true</lticm:property>
                <lticm:property name="message_type">ContentItemSelectionRequest</lticm:property>
          </lticm:options>
XML;

            $varsXml = <<<XML
            <blti:custom>
                    <lticm:property name="custom_canvas_api_domain">\$Canvas.api.domain</lticm:property>
					<lticm:property name="custom_canvas_course_id">\$Canvas.course.id</lticm:property>
					<lticm:property name="custom_canvas_user_id">\$Canvas.user.id</lticm:property>
					<lticm:property name="custom_canvas_user_login_id">\$Canvas.user.loginId</lticm:property>
					<lticm:property name="lis_person_contact_email_primary">\$Person.email.primary</lticm:property>
					<lticm:property name="user_image">\$User.image</lticm:property>
					<lticm:property name="lis_course_offering_sourcedid">\$CourseSection.sourcedId</lticm:property>
					<lticm:property name="lis_person_sourcedid">\$Person.sourcedId</lticm:property>
            </blti:custom>
XML;
        }


        $string = <<<XML
<?xml version='1.0' encoding='UTF-8'?>
<!--If you would like to run this page outside of Canvas for testing purposes please add an instance of the dev component instead of the LTI Configuration component-->
<cartridge_basiclti_link xmlns='http://www.imsglobal.org/xsd/imslticc_v1p0'
    xmlns:blti = 'http://www.imsglobal.org/xsd/imsbasiclti_v1p0'
    xmlns:lticm ='http://www.imsglobal.org/xsd/imslticm_v1p0'
    xmlns:lticp ='http://www.imsglobal.org/xsd/imslticp_v1p0'
    xmlns:xsi = 'http://www.w3.org/2001/XMLSchema-instance'
    xsi:schemaLocation = 'http://www.imsglobal.org/xsd/imslticc_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticc_v1p0.xsd
    http://www.imsglobal.org/xsd/imsbasiclti_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imsbasiclti_v1p0.xsd
    http://www.imsglobal.org/xsd/imslticm_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticm_v1p0.xsd
    http://www.imsglobal.org/xsd/imslticp_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticp_v1p0.xsd'>
    <blti:title>$linkTitle</blti:title>
    <blti:description>$desc</blti:description>
    <blti:icon>$favicon</blti:icon>
    <blti:launch_url>$url</blti:launch_url>
    <blti:extensions platform='canvas.instructure.com'>
          <lticm:property name='tool_id'>$linkTitle</lticm:property>
          <lticm:property name='privacy_level'>public</lticm:property>
          <lticm:property name='domain'>$domain</lticm:property>
                $typeXML
    </blti:extensions>
    $varsXml
    <cartridge_bundle identifierref='BLTI001_Bundle'/>
    <cartridge_icon identifierref='BLTI001_Icon'/>
</cartridge_basiclti_link>
XML;

        $xml = new \SimpleXMLElement($string);
        header("Content-type: text/xml; charset=utf-8");
        echo $xml->asXML();
        die();
    }

}
