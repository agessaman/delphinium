<?php namespace Delphinium\Iris\Classes;

use Delphinium\Raspberry\Classes\Api;
use Delphinium\Raspberry\Models\OrderedModule;
use Exception;


/*
 * This class will retrieve the necessary information from Raspberry to build out the iris chart.
 */
class Iris 
{

	public function getModules($courseId, $token, $cacheTime, $forever)
	{
		//TODO: make this domain and courseId configurable
            
                //until I fix the token, we'll use this one
            $token = '14~U2NLr7L2YmFsapN53ovxT6kvK4eToJL8LvuO2QZj1j8XAMLIlM1Yokz8CtKL8gxY';
		$url = 'https://uvu.instructure.com/api/v1/courses/'.$courseId.'/modules?include[]=items&include[]=content_details&access_token='.$token.'&per_page=5000';
		$api = new Api();
		
		//return an array of Module objects
		$data = $api->getModules($url, $courseId, true, $cacheTime, $forever);
		
		
		return $data;
	}
        
        public function saveIrisModules($array, $courseId, $cacheTime)
        {
            $api = new Api();
            $api->saveIrisModules($courseId, $array, $cacheTime);
        }
        
        public function getCourse($courseId)
        {
            $api = new Api();
            $return = $api->getCourse($courseId);
            return $return;
        }
        
        public function getAvailableTags($courseId)
        {
            $api = new Api();
            $return = $api->getAvailableTags($courseId);
            return $return;
        }
}