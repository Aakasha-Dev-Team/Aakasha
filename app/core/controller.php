<?php namespace core;
use core\view as View,
    helpers\globals as Globals;

/*
 * controller - base controller
 *
 * @author David Carr - dave@daveismyname.com - http://www.daveismyname.com
 * @version 2.1
 * @date June 27, 2014
 */
class Controller {

    protected $view;

    /**
     * on run make an instance of the config class and view class
     */
    public function __construct(){
        //initialise the views object
        $this->view = new view();
    }

}
