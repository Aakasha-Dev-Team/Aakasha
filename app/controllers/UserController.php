<?php namespace controllers;
use models\Authentication as Authentication,
    models\Department as Department,
    models\User as User,
    models\Bookmark as Bookmark,
    helpers\util as Util,
    helpers\request as Request,
    libraries\password as Password;
use models\Log;

/**
 * [name] controller
 * @author
 */

class UserController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
    * call the parent construct
    */
    
    public function showProfile() {
        
        $sessionId = \helpers\Session::get('user_id');

        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");

        $objUser  = new \models\User($sessionId);
        $username = $objUser->getName();
        $avatar   = $objUser->getAvatar();
        $email    = $objUser->getEmail();
        $phone            = $objUser->getPhone();
        $joined_in_firm   = $objUser->getJoinedInFirm();
        $birthday         = $objUser->getBirthday();
        $position_in_firm = $objUser->getPositionInFirm();
        
        $last_signin = $objUser->getLastSigIn();
        
        $dateTime = new \DateTime($last_signin);
        $date     = $dateTime->format("F j, Y, g:i a");

        //Fetch Department
        $objDepartment = new Department($objUser->department_id);
        $department = $objDepartment->getName();

        //Fetch logs
        $logs = Log::fetchLastUserLogs($objUser->getId());
        
        //Track last action
        User::trackLastAction();
        
        //Add information in template

        $this->view->addContent([
            "title"                 => "Profile",
            "Home"                  => _T("Home"),
            "My profile"            => _T("My profile"),
            "Edit profile"          => _T("Edit profile"),
            "Mail"                  => _T("Mail"),
            "Personal phone"        => _T("Personal phone"),
            "Work phone"            => _T("Work phone"),
            "Job position"          => _T("Job position"),
            "Department"            => _T("Department"),
            "Last seen"             => _T("Last seen"),
            "Joined the company"    => _T("Joined the company"),
            "Birthday"              => _T("Birthday"),
            "Profile"               => _T("Profile"),
            "Recent Activities"     => _T("Recent Activities"),
            "username"              => $username,
            "avatar"                => $avatar,
            "email"                 => $email,
            "day_time_last_signin"  => $date,
            'phone'                 => $phone,
            'joined_in_firm'        => $joined_in_firm,
            'birthday'              => $birthday,
            'position_in_firm'      => $position_in_firm,
            'department'            => $department,
            'logs'                  => $logs,
            'id'                    => $sessionId
        ]);
        
        //jQuery
        $jq = "";
        
        //Add jq to template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("user/show_profile");
    }
    
    /**
    * call the parent construct
    */ 
    public function showAddEditProfile($slug) {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //If is admin
        //Authentication::isAdmin("exit");
        
        //Track last action
        User::trackLastAction();
        
        $objDepartment = new Department();
        $arrDepartment = $objDepartment->fetchDepartments();
        
        //\helpers\util::dbug($arrDepartment);
        
        if ($slug != 0) {
            $objUser    = new User($slug);
            $name       = $objUser->getName();
            $arrName    = explode(" ", $name);
            $username   = $objUser->getUsername();
            $email      = $objUser->getEmail();
            $department = $objUser->getDepartmentId();
            $avatar     = $objUser->getAvatar();
            $phone            = $objUser->getPhone();
            $joined_in_firm   = $objUser->getJoinedInFirm();
            $birthday         = $objUser->getBirthday();
            $position_in_firm = $objUser->getPositionInFirm();
            
            $this->view->addContent([
                'fname'    => $arrName[0],
                'lname'    => $arrName[1],
                'email'    => $email,
                'username' => $username,
                'user_id'  => $slug,
                'avatar'   => "/uploads/avatars/" . $avatar,
                'phone'    => $phone,
                'joined_in_firm' => $joined_in_firm,
                'birthday' => $birthday,
                'position_in_firm' => $position_in_firm
            ]);
        } else {
            $this->view->addContent([
                'fname'    => "",
                'lname'    => "",
                'email'    => "",
                'username' => "",
                'user_id'  => "",
                'avatar'   => "http://placehold.it/150"
            ]);
        }
        
        //Add information in template
        $this->view->addContent([
            "title" => "Profile",
            "Home"                       => _T("Home"),
            "Edit_Create user"           => _T("Edit/Create user"),
            "First name"                 => _T("First name"),
            "Last name"                  => _T("Last name"),
            "Email"                      => _T("Email"),
            "Personal phone"             => _T("Personal phone"),
            "Work phone"                 => _T("Work phone"),
            "Job position"               => _T("Job position"),
            "Department"                 => _T("Department"),
            "Last seen"                  => _T("Last seen"),
            "Joined the company"         => _T("Joined the company"),
            "Birthday"                   => _T("Birthday"),
            "Username"                   => _T("Username"),
            "Password"                   => _T("Password"),
            "Confirm password"           => _T("Confirm password"),
            "Upload a different photo"   => _T("Upload a different photo"),
            "departments"                => $arrDepartment,
            "Picture"                    => _T("Picture")
        ]);
        
        //jQuery
        $jq = "
            //set user section
            var department = '" . $department . "';
            
            //simulate click for upload
            $('#dummy_click').click(function(){
                $('#file').click();
            });
            
            $('#department option').each(function(index) {
                var option = $(this).val();
                
                if (department == option) {
                    $(this).prop('selected', true);
                }
            });
            
            $('#user-form').on('submit', function(event) {
                event.preventDefault();
                var formData = new FormData($(this)[0]);
                load.show();
                
                $.ajax({
                    url: '/user/add_edit_user_ajax',
                    type: 'POST',
                    data: formData,
                    async: false,
                    cache: false,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    complete: function (data) {
                        var response = JSON.parse(data.responseText);
                        modal(response.title, response.body);
                        load.hide();
                    }
                });
                    
            });
        ";
        
        //Add jq to template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("user/add_edit_profile");
    }
    
    /**
     * Add or edit user
     * @author realdark <me@borislazarov.com> on 27 Jan 2015
     * @return json
     */
    public function addEditUserAjax() {
        //Track last action
        User::trackLastAction();
        
        $is_valid = \libraries\gump::is_valid($_POST, array(
            'fname'            => 'required|alpha',
            'lname'            => 'required|alpha',
            'username'         => 'required|alpha_numeric',
            'email'            => 'required|valid_email',
            'department'       => 'required|numeric'
        ));
        
        //if information from user is valid continue otherwise display msg
        if($is_valid === true) {
            $fname            = $_POST['fname'];
            $lname            = $_POST['lname'];
            $username         = $_POST['username'];
            $password         = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            $email            = $_POST['email'];
            $department       = $_POST['department'];
            $userId           = $_POST['user_id'] == "" ? 0 : $_POST['user_id'];
            $phone            = $_POST['phone'];
            $joined_in_firm   = $_POST['joined_in_firm'];
            $birthday         = $_POST['birthday'];
            $position_in_firm = $_POST['position_in_firm'];
            
            if ($password == $confirm_password) {
                
                //new user
                    if ($userId == 0) {
                    $objUser = new User();
                    $objUser->setIsAdmin(0);
                //edit user
                } else {
                    $objUser = new User($userId);
                }
                
                $objUser->setName($fname . " " . $lname);
                $objUser->setUsername($username);
                
                $encrtptedPassword = Password::make($password);
                
                if (!empty($password)) {
                    $objUser->setPassword($encrtptedPassword);
                }

                //set phone
                if (isset($phone)) {
                    $objUser->setPhone($phone);
                }

                //set joined in firm
                if (isset($joined_in_firm)) {
                    $objUser->setJoinedInFirm($joined_in_firm);
                }

                //set birthday
                if (isset($birthday)) {
                    $objUser->setBirthday($birthday);
                }

                //set position in firm
                if (isset($position_in_firm)) {
                    $objUser->setPositionInFirm($position_in_firm);
                }
                
                $objUser->setEmail($email);
                $objUser->setDepartmentId($department);
                
                if ($userId == 0) {
                    $objUser->set_expr('created', 'NOW()');
                }
                
                //uplaod avatar
                if (isset($_FILES['file']['name'])) {
                    $uniqid = uniqid();
                    
                    $uploaddir     = ROOT_PATH . "uploads/avatars/";
                    $fileExtension = explode(".", $_FILES['file']['name']);
                    $uploadfile    = $uploaddir . $uniqid . "." . end($fileExtension);
                    $allowedtypes  = ["jpg", "png", "jpeg", "gif"];
                    
                    if (in_array(end($fileExtension), $allowedtypes)) {
                        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
                            $objUser->setAvatar($uniqid . "." . end($fileExtension));
                        }
                    }
                }
                
                try {
                    $idUser = $objUser->save();
                } catch (\Exception $e) {
                    \core\logger::exception_handler($e);
                }
                
                //set permissions
                if ($userId == 0) {
                    $objPermission = new \models\Permission();
                    $permissions = $objPermission->fetchDepartmentPermissions($department);
                    
                    foreach ($permissions as $permission) {
                        //Get parrent id
                        $objPermissionSection = new \models\PermissionSection(['name' => $permission['main_department'], 'parent' => 0], ['id']);
                        $paretntId            = $objPermissionSection->getId();
                        
                        $objPermissionSection = new \models\PermissionSection(['name' => $permission['name'], 'parent' => $paretntId], ['id']);
                        $slaveId              = $objPermissionSection->getId();
                        
                        $objPermission = new \models\Permission();
                        $objPermission->setMainSectionId($paretntId);
                        $objPermission->setSectionId($slaveId);
                        $objPermission->setUserId($idUser);
                        $objPermission->setPermission($permission['permission']);
                        $objPermission->setView($permission['view']);
                        $objPermission->setEdit($permission['edit']);
                        
                        try {
                            $objPermission->save();
                        } catch (\Exception $e) {
                            \core\logger::exception_handler($e);
                        }
                    }
                }
                
                if ($userId == 0) {
                    //Logger
                    Log::logMe("Created new user with name " . $fname . ' ' . $lname);

                    Util::modal(true, _T("Success"), _T("The user was created!"));
                } else {
                    //Logger
                    Log::logMe("Updated user with name " . $fname . ' ' . $lname);

                    Util::modal(true, _T("Success"), _T("The user was successfully updated!"));
                }
                
                
            } else {
                Util::modal(true, _T("Error"), _T("The passwords didn't match, try again!"));
            }
            
        } else {
            Util::modal(true, _T("Error"), $is_valid);
        }
    }
    
    /**
     * Bookmarks
     * @author realdark <me@borislazarov.com> on 23 Feb 2015
     * @return void
     */
    public function bookmarks() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        $bookmarks = Bookmark::fetchBookmarks();
        
        //Add information in template
        $this->view->addContent([
            "title"              => _T("Bookmarks"),
            "Home"               => _T("Home"),
            "Bookmarks"          => _T("Bookmarks"),
            "User"               => _T("User"),
            "Add More Bookmarks" => _T("Add More Bookmarks"),
            "Save"               => _T("Save"),
            "Name"               => _T("Name"),
            "Link"               => _T("Link"),
            'Remove'             => _T("Remove"),
            "default_name"       => $bookmarks[0]['name'],
            "default_link"       => $bookmarks[0]['link'],
        ]);
        
        $totoalBookmarks = count($bookmarks);
        $this->view->addContent("bookmarks_count", $totoalBookmarks);
        
        if ($totoalBookmarks > 1) {
            unset($bookmarks[0]);
            $this->view->addContent("bookmarks_data", $bookmarks);
        } else {
            $this->view->addContent("bookmarks_data", []);
        }
        
        //jQuery
        $jq = "
        var max_fields      = 10; //maximum input boxes allowed
        var wrapper         = $('.input_fields_wrap'); //Fields wrapper
        var add_button      = $('.add_field_button'); //Add button ID
       
        var x = 1; //initlal text box count
        $('#add').click(function(e){ //on add input button click
            e.preventDefault();
            if(x < max_fields){ //max input box allowed
                x++; //text box increment
                $(wrapper).append('<div>\
                    <span class=\"col-md-3 form-group\">\
                        <input type=\"text\" class=\"form-control\" name=\"bookmarks_name[]\" placeholder=\"{Name}\"/>\
                    </span>\
                    <span class=\"col-md-7 form-group\">\
                        <input type=\"text\" class=\"form-control\" name=\"bookmarks_link[]\" placeholder=\"{Link}\"/>\
                    </span>\
                    <span class=\"col-md-2\">\
                        <a href=\"#\" class=\"remove_field\">{Remove}</a>\
                    </span>\
                </div>'); //add input box
            }
        });
       
        $('.input_fields_wrap').on('click','.remove_field', function(e){ //user click on remove text
            e.preventDefault(); $(this).parent().parent('div').remove(); x--;
        })
        
        $('#save').click(function(event) {
            event.preventDefault();
        
            $.post('/user/bookmarks_ajax', $('form').serialize(), function(data) {
                modal(data.title, data.body);
            }, 'json')
        });
        ";
        
        //Add jq to template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("user/bookmarks");
    }
    
    /**
     * Save bookmarks in db
     * @author realdark <me@borislazarov.com> on 23 Feb 2015
     * @return void
     */
    public function bookmarksAjax() {
        //Track last action
        User::trackLastAction();
        
        $names = Request::get('bookmarks_name', 'array');
        $links = Request::get('bookmarks_link', 'array');

        //Logger
        Log::logMe("Added new bookmark");
        
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        if (isset($userId)) {
            //clean old data
            $objBookmark = new Bookmark(['user_id' => $userId]);
            
            Bookmark::deleteBookmarks();
            
            //add new data
            foreach ($names as $key => $name) {
                if (!empty($name) && !empty($links[$key])) {
                    $objBookmark = new Bookmark();
                    $objBookmark->setUserId($userId);
                    $objBookmark->setName($name);
                    $objBookmark->setLink($links[$key]);
                    
                    try {
                        $bookId = $objBookmark->save();
                    } catch (\Exception $e) {
                        \core\logger::exception_handler($e);
                    }
                }
            }
            
            Util::modal(true, _T("Success"), _T("The bookmarks was successfully updated!"));
        } else {
            Util::modal(true, _T("Error"), _T("You are not logged!"));
        }
    }
}