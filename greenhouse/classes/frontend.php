<?php namespace delphinium\greenhouse\classes;

use Delphinium\Blossom\Models\Experience;
use Cms\Classes\Controller;
use Cms\Classes\CmsObject;

class frontend 
{//TODO get this to run in a page, along with the green house partial -- TODO make adding a component to this dynamic, something that a component author can do from their component
	public function launchGreenhouse(){
		$controller = Controller::getController() ?: new Controller;
		if(isset($_POST['lti_message_type'])){
			$this['messageType']=$_POST['lti_message_type'];

			switch ($this['messageType']) {
		
				case "ContentItemSelectionRequest": //Present text editor dialog to select a component
					$this['return_url'] = $_POST["content_item_return_url"];
					break;

				case "basic-lti-launch-request": //load requested component
					$this->loadComponents();
					break;
			}
		} else { //return xml configuration file for LTI tool
			$this->returnXML($controller);
		}
	}

	private function loadComponents() {
		
		$properties = ['ltiInstance' => $_POST['ltiInstance'] , 'approver'=>$_POST['approver']];
		$name = 'LtiConfiguration';
		$alias = '';
		$this->addComponent($name, $alias, $properties );

		$properties = json_decode($_POST['properties'], true);
		if (!isset($properties)) {$properties = array();};
		$name = $_POST['component'];
		$alias = 'alias';
		$this->addComponent($name, $alias, $properties );
	}

	private function onCreateComponentInstance() { 
		if(post('component') == "experience"){// get component type from js
			$experience = new Experience;
			$experience->name = 'Experience';
			$experience->total_points = 1000;
			$experience->bonus_per_day = 1;
			$experience->penalty_per_day = 1;
			$experience->bonus_days = 5;
			$experience->penalty_days = 25;
			$experience->start_date = "Mon, Aug 31, 2015 12:00 AM";
			$experience->end_date = "Thu, Dec 17, 2015 12:00 AM";
			$experience->animate = 1;
			$experience->size = "medium";
			$experience->save();
			$instanceID = $experience->id;
		}        
		return [
			'instanceID' => $instanceID
		];        
	}

private function returnXML($controller) {
    $url = $controller->pageUrl($this->page->getFileName());
        
    $string = <<<XML
<cartridge_basiclti_link xmlns="http://www.imsglobal.org/xsd/imslticc_v1p0" xmlns:blti="http://www.imsglobal.org/xsd/imsbasiclti_v1p0" xmlns:lticm="http://www.imsglobal.org/xsd/imslticm_v1p0" xmlns:lticp="http://www.imsglobal.org/xsd/imslticp_v1p0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imslticc_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticc_v1p0.xsd http://www.imsglobal.org/xsd/imsbasiclti_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imsbasiclti_v1p0p1.xsd http://www.imsglobal.org/xsd/imslticm_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticm_v1p0.xsd http://www.imsglobal.org/xsd/imslticp_v1p0 http://www.imsglobal.org/xsd/lti/ltiv1p0/imslticp_v1p0.xsd">
	<blti:title>Greenhouse</blti:title>
	<blti:description>Embed Delphinium Components</blti:description>
	<blti:launch_url>$url</blti:launch_url>
	<blti:extensions platform="canvas.instructure.com">
		<lticm:property name="domain">delphinium.uvu.edu</lticm:property>
		<lticm:options name="editor_button">
			<lticm:property name="message_type">ContentItemSelectionRequest</lticm:property>
			<lticm:property name="url">$url</lticm:property>
		</lticm:options>
		<lticm:property name="icon_url">https://delphinium.uvu.edu/octobercms/themes/demo/assets/images/favicon.png</lticm:property>
		<lticm:property name="selection_height">800</lticm:property>
		<lticm:property name="selection_width">600</lticm:property>
	</blti:extensions>
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
</cartridge_basiclti_link>
XML;

    $xml = new SimpleXMLElement($string);
    
    echo $xml->asXML();
    die();
}
}
