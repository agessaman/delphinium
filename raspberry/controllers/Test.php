<?php namespace Delphinium\Raspberry\Controllers;

use BackendMenu;
use BackendAuth;
use Illuminate\Routing\Controller;

class Test extends Controller {
	
	public function index()
	{
		return "Hi, API";
	}
}