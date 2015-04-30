<?php namespace controllers;
use models\Authentication as Authentication,
    models\User as User,
    helpers\util as Util;
use models\Log;

/**
 * Authentication controller
 * @author realdark <me@borislazarov.com>
 */
class AuthenticationController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
    * Sign in page
    * @author realdark <me@borislazarov.com>
    * @return string
    */ 
    public function index() {
        //Track last action
        User::trackLastAction();
        
        //Add information in template
        $this->view->addContent([
            "title"          => _T("Sign In Page"),
            "Please sign in" => _T("Please sign in"),
            "Username"       => _T("Username"),
            "Password"       => _T("Password"),
            "Login"          => _T("Login")
        ]);
        
        //jQuery
        $jq = "
            alert('Приложението е в тестов период и не бива да се ползва в реални условия. Ако забележите бъгове или имате предложение пуснете тикет на wf.kinyx.biz')

            //Sent user information through ajax to signInAjax() php function
            $('form').submit(function(event) {
                event.preventDefault();
                
                var formData = $('form').serialize();
                
                //Avtivate busy mouse
                load.show();
                
                $.post('/user/sign_in_ajax', formData, function(data) {
                    //Return to normal mouse
                    load.hide();
                    
                    if (data.status == true) {
                        //Activate modal
                        modal(data.title, data.body, 'error');
                    } else {
                        //rediect to home page
                        window.location.href = '/';
                    }
                }, 'json');
            });
        ";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage('user/index');
    }
    
    /**
     * Processing information submitted by user
     * @author realdak <me@borislazarov.com>
     * @return json
     */
    public function signInAjax() {
        //Track last action
        User::trackLastAction();
        
        $is_valid = \libraries\gump::is_valid($_POST, array(
            'username' => 'required|alpha_numeric',
            'password' => 'required|max_len,100|min_len,3'
        ));
        
        //if information from user is valid continue otherwise display msg
        if($is_valid === true) {
            $userInformation['username'] = $_POST['username'];
            $userInformation['password'] = $_POST['password'];
            
            $objAuthentication = new Authentication();
            
            //Processing username and password.
            $status = $objAuthentication->authentication($userInformation);
            
            //If found a error display msg otherwise continue
            if ($status == false) {
                Util::modal(true, _T("Error"), _T("The user name or/and password is incorrect"));
            } else {
                //Logger
                Log::logMe("User was successfully signed in");

                Util::modal(false);
            }
            
        } else {
            Util::modal(true, _T("Error"), $is_valid);
        }
    }
    
    /**
     * Log out page
     * @author Bobi <me@borislazarov.com> on 14 Oct 2014
     * @return redirect
     */
    public function logOut() {
        //Track last action
        User::trackLastAction();

        //Logger
        Log::logMe("User was successfully signed out");
        
        $objAuthentication = new Authentication();
        $objAuthentication->logOut();
        header('Location: /');

        exit();
    }
}