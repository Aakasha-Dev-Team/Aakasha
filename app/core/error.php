<?php namespace core;
use core\controller as Controller,
    helpers\session as Session;

/*
 * error class - calls a 404 page
 *
 * @author David Carr - dave@daveismyname.com - http://www.daveismyname.com
 * @version 2.1
 * @date June 27, 2014
 */
class Error extends Controller {
    
    public function __construct(){
	parent::__construct();
    }

    /**
     * load a 404 page with the error message
     */
    public function index() {

	header("HTTP/1.0 404 Not Found");
	
	$vars = $this->view->getTemplateVars("error");
	
	foreach ($vars as $key => $value) {
	    $this->view->addContent($key, $value);
	}
	
	$this->view->loadPage("error/404");
	    
    }

    /**
     * display errors
     * @param  array  $error an error of errors
     * @param  string $class name of class to apply to div
     * @return string        return the errors inside divs
     */
    static public function display(array $error, $class = 'alert alert-danger') {
	$errorrow = null;

	if (is_array($error)){

	    foreach($error as $error){
		    $errorrow.= "<div class='$class'>".$error."</div>";
	    }

	   return $errorrow;
	
	}
    }

}
