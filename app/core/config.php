<?php namespace core;
use helpers\session as Session,
    helpers\globals as Globals,
    models\Locale as Locale,
    libraries\phpfastcache\phpfastcache as Phpfastcache,
    libraries\psr\Log\LogLevel as LogLevel,
    libraries\parser as Parser;

/*
 * config - an example for setting up system settings
 * When you are done editing, rename this file to 'config.php'
 *
 * @author David Carr - dave@daveismyname.com - http://www.daveismyname.com
 * @author Edwin Hoksberg - info@edwinhoksberg.nl
 * @version 2.1
 * @date June 27, 2014
 */
class Config {

    public function __construct() {
	//turn on output buffering
	ob_start();
	
	//start sessions
	Session::init();

	//site address
	define('DIR', 'http://appdev.aakasha.com/');

	//paths
	define('ROOT_PATH', getcwd() . '/');
	define('APP_PATH', ROOT_PATH . "app/");
	
	//set default controller and method for legacy calls
	define('DEFAULT_CONTROLLER', 'MainController');
	define('DEFAULT_METHOD' , 'index');

	//set a default language
	define('LANGUAGE', 'bg_BG');

	//database details ONLY NEEDED IF USING A DATABASE
	define('DB_TYPE', 'mysql');
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'appdev');
	define('DB_USER', 'appdev');
	define('DB_PASS', 'Fu7sJvDQvZzUTYq9');
	define('PREFIX', 'app_');

	//set prefix for sessions
	define('SESSION_PREFIX', 'app_');

	//optionall create a constant for the name of the site
	define('SITETITLE', 'Aakasha App');

    //turn on custom error handling
    set_exception_handler('core\logger::exception_handler');
    set_error_handler('core\logger::error_handler');
	
	//User permissions
	Globals::set("orders_permissions", \models\Permission::permission("Orders"));
	Globals::set("invoices_permissions", \models\Permission::permission("Invoices"));
	Globals::set("history_permissions", \models\Permission::permission("History"));
    Globals::set("storehouse_permissions", \models\Permission::permission("Storehouse"));
	
	//initialise the parser
	Globals::set("parser", new Parser());
	
	//set timezone
	date_default_timezone_set('Europe/Sofia');

	//set the default template
	Session::set('template', 'sbydev_realdark'); //Default template: default

	//configure cache
	Phpfastcache::setup("storage","auto");

	//includes
	include_once("locale.php"); //Locale core
	include_once(APP_PATH . "etc/templates.php"); //Template vars
    }

}
