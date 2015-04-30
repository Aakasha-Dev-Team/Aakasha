<?php namespace helpers;

/**
* Class for global varables
* @author Bobi <me@borislazarov.com> on 25 Oct 2014
*/
class Globals {
	
    //here will be stored varables
    private static $vars = array();

    /**
     * Sets the global one time.
     * @author M@dHatter on 21 May 2014
     * @source http://www.codeproject.com/Tips/776167/PHP-Global-Variable-Alternative
     * @param string $_name  Name of global
     * @param mixed  $_value Value
     */
    public static function set($_name, $_value) {
        if(array_key_exists($_name, self::$vars)) {
            throw new \Exception('globals::set("' . $_name . '") - Argument already exists and cannot be redefined!');
        } else {
            self::$vars[$_name] = $_value;
        }
    }

    /**
     * Get the global to use.
     * @author M@dHatter on 21 May 2014
     * @source http://www.codeproject.com/Tips/776167/PHP-Global-Variable-Alternative
     * @param  string $_name Name of global
     * @return mixed   		 Value
     */	
    public static function get($_name) {
        if(array_key_exists($_name, self::$vars)) {
            return self::$vars[$_name];
        } else {
            throw new \Exception('globals::get("' . $_name . '") - Argument does not exist in globals!');
        }
    }

}
