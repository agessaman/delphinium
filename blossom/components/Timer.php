<?php namespace Delphinium\Blossom\Components;

use Cms\Classes\ComponentBase;
use Delphinium\Roots\Roots;
use \DateTime;
use \DateInterval;

class Timer extends ComponentBase
{

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
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/timer.js");
        $this->addJs("/plugins/delphinium/blossom/assets/javascript/d3.min.js");
        $this->addCss("/plugins/delphinium/blossom/assets/css/main.css");
        $this->addCss("/plugins/delphinium/blossom/assets/css/timer.css");

        if(!isset($_SESSION)) 
        { 
            session_start(); 
    	}
        $courseId = $_SESSION['courseID'];
        
        $this->roots = new Roots();
        
         try {
            $enrollments = $this->roots->getEnrollments();
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

        } 
        catch (\GuzzleHttp\Exception\ClientException $e) 
        {
            $end = new DateTime("now");
            $this->page['start'] = $end->format('c');
            $this->page['end'] = $end->format('c');
            echo "In order for the 'Timer' app to run properly you must be a student or you must go in 'Student View'";
            return;
        }
        
    
    }

    
        

}