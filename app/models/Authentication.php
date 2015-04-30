<?php namespace models;
use libraries\password as Password,
    helpers\session as Session;
    
/**
 * Authentication model
 * @author
 */
class Authentication extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Authentication for users
     * @author Bobi <me@borislazarov.com> on 9 Dec 2014
     * @return bollean
     */
    public function authentication($userInformation = [], $type = NULL) {
            
            //check if username exist
            $objUser = new User(["username" => $userInformation['username']], ["id", "password"]);

            if ($objUser != false) {
                $id       = $objUser->id;
                $password = $objUser->password;
    
                if(Password::verify($userInformation['password'], $password)) {
                    Session::set('loggin', true);
                    Session::set('user_id', $id);
                    $status = true;
                } else {
                    $status = false;
                }
            }

        return $status;

    }
    
    /**
     * Check whether the user has rights
     * @author Bobi <me@borislazarov.com> on 9 Dec 2014
     * @return boolean
     */
    public static function chechAuthentication($type = NULL) {
        $logged = Session::get('loggin');

        switch ($type) {
            case 'exit':
                $logged == false ? header('Location: /user/sign_in') : $logged;
                break;
            
            case 'status':
                $logged == false ? 0 : 1;
                break;
            
            default:
                $logged;
                break;
        }

        return $logged;
    }

    /**
     * Log out from site
     * @author Bobi <me@borislazarov.com> on 14 Oct 2014
     * @return boolean
     */
    public function logOut() {
        Session::destroy();
    }
    
    /**
     * Check for admin rights
     * @author realdark <me@borislazarov.com> on 13 Jan 2015
     * @return boolean
     */
    public static function isAdmin($type = NULL) {
        $userId = Session::get('user_id');
        
        $objUser = new User($userId, ['is_admin']);
        $isAdmin = $objUser->getIsAdmin();
        
        switch ($type) {
            case "exit":
                if ($isAdmin == 0) {
                    header('Location: /');
                } else {
                    return true;
                }
                break;
            
            default:
                if ($isAdmin == 1) {
                    return true;
                } else {
                    return false;
                }
                break;
        }
        
    }
    
}