<?php namespace helpers;

/*
 * Cookie Class - prefix cookie with useful methods
 *
 * @author Bobi <me@borislazarov.com>
 * @version 1.0
 * @date 21 Nov 2014
 */
class Cookie {

    /**
     * Set cookie
     * @author Bobi <me@borislazarov.com> on 20 Nov 2014
     * @param $key   Name of cookie
     * @param $value Value of cookie
     * @return void
     */
    public static function set($key, $value = false) {
        if(is_array($key) && $value === false){

            foreach ($key as $name => $value) {
                setcookie($key, $value);
            }

        } else {
            setcookie($key, $value);
        }
    }
    
    /**
     * Get cookie
     * @author Bobi <me@borislazarov.com> on 20 Nov 2014
     * @param $key       Name of cookie
     * @param $secondkey Secondary name
     * @return mixed
     */
    public static function get($key, $secondkey = false) {
        if($secondkey == true){

            if(isset($_COOKIE[$key][$secondkey])){
                return $_COOKIE[$key][$secondkey];
            }

        } else {

            if(isset($_COOKIE[$key])){
                return $_COOKIE[$key];
            }

        }

        return false;
    }

}
