<?php namespace controllers;
use models\Authentication as Authentication,
    models\User as User,
    models\Permission as Permission,
    models\Department as Department,
    helpers\request as Request,
    helpers\util as Util;
use models\Log;

/**
 * Permission controller
 * @author realdark <me@borislazarov.com> on 13 Jan 2015
 */
class PermissionController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }


    /**
     * Display permission page
     * @author realdark & SBYDev on 15 Jan 2015
     * @return void
     */
    public function displayUsersTable() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //If is admin
        Authentication::isAdmin("exit");

        //Logger
        Log::logMe("Saw user table");

        //Track last action
        User::trackLastAction();
        
        //Add information in template
        $this->view->addContent([
            "title"             => _T("Display Users"),
            "Home"              => _T("Home"),
            "User rights"       => _T("User rights"),
            "Show users"        => _T("Show users"),
            "List of users"     => _T("List of users"),
            "Select department" => _T("Select department"),
            "All"               => _T("All"),
            "Production"        => _T("Production"),
            "Packaging"         => _T("Packaging"),
            "Sales"             => _T("Sales"),
            "Management"        => _T("Management"),
            "Show"              => _T("Show"),
            "Name"              => _T("Name"),
            "Mail"              => _T("Mail"),
            "Actions"           => _T("Actions"),
            "Permissions"       => _T("Permissions"),
            "Profile"           => _T("Profile"),
            "It Department"     => _T("It Department")
        ]);
        
        //Object User
        $objUser = new User();
        $arrUser = $objUser->fetchUsers();
        
         $this->view->addContent("users", $arrUser);
        
        //jQuery
        $jq = "
            $('#show-by-departemnts').click(function(event) {
                event.preventDefault();
                var department = $('#departments :selected').val();
                var string     = '';
                
                //clear table
                $('table tbody').empty();
                
                $.post('/permisions/users_table_ajax', {
                    department : department
                }, function(data) {
                
                    $.each(data, function(key, value) {
                        string += '\
                            <tr>\
                                <td>' + value.name + '</td>\
                                <td>' + value.email + '</td>\
                                <td><a href=\'/permisions/user_view/' + value.id + '\' class=\'btn btn-default btn-block\' title=\'User\'>" . _T("View") . "</a></td>\
                            </tr>\
                        ';
                    });
                    
                    $('table tbody').html(string);
                    
                }, 'json');
            });
        ";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("permisions/users_table");
    }
    
    /**
     * Fetch users by department
     * @author realdark <me@borislazarov.com> on 20 Jan 2015
     * @return json
     */
    public function usersTableAjax() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //If is admin
        Authentication::isAdmin("exit");
        
        //Track last action
        User::trackLastAction();
        
        $search = Request::get("department", "integer");
        
        //Object User
        $objUser = new User();
        $arrUser = $objUser->fetchUsers($search);
        
        echo json_encode($arrUser);
    }
    
    /**
     * Display permission page
     * @author realdark & SBYDev on 15 Jan 2015
     * @return void
     */
    public function displayUserView($slug) {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //If is admin
        Authentication::isAdmin("exit");
        
        //Track last action
        User::trackLastAction();
        
        //Fetch Permissions
        $objPermission = new Permission();
        $arrPermissions = $objPermission->fetchPermissions($slug);
        //\helpers\util::dbug($arrPermissions);
        foreach ($arrPermissions as $permission) {
            switch ($permission['name']) {
                case "edit_order":
                    if ($permission['view'] == 1 && ($permission['edit'] == 0)) {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_view", "checked");
                    } else {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_edit", "checked");
                    }
                    break;
                default:
                    if ($permission['permission'] == 0) {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_none", "checked");
                    } else {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_view", "checked");
                    }
                    break;
            }
        }
        
        //Add information in template
        $this->view->addContent([
            "title"                                                                 => _T("Display User"),
            "Home"                                                                  => _T("Home"),
            "User rights"                                                           => _T("User rights"),
            "Show users"                                                            => _T("Show users"),
            "UserName"                                                              => _T("UserName"),
            "History"                                                               => _T("History"),
            "Invoices history"                                                      => _T("Invoices history"),
            "allowes user to see created invoices"                                  => _T("allowes user to see created invoices"),
            "No"                                                                    => _T("No"),
            "View"                                                                  => _T("Views"),
            "Yes"                                                                   => _T("Yes"),
            "View & Edit"                                                           => _T("View & Edit"),
            "Departments"                                                           => _T("Departments"),
            "allowes user to see actions of users in departments"                   => _T("allowes user to see actions of users in departments"),
            "Orders"                                                                => _T("Orders"),
            "allowes users to see the actions of users for orders"                  => _T("allowes users to see the actions of users for orders"),
            "Invoices"                                                              => _T("Invoices"),
            "Show orders"                                                           => _T("Show orders"),
            "allowes user to see orders"                                            => _T("allowes user to see orders"),
            "Generate"                                                              => _T("Generate"),
            "allowes user to generate invoices"                                     => _T("allowes user to generate invoices"),
            "Orders"                                                                => _T("Orders"),
            "Upload orders"                                                         => _T("Upload orders"),
            "allowes user to upload new orders"                                     => _T("allowes user to upload new orders"),
            "Upload customers"                                                      => _T("Upload customers"),
            "allowes user to upload new customers"                                  => _T("allowes user to upload new customers"),
            "See orders"                                                            => _T("See orders"),
            "allowes user to see orders"                                            => _T("allowes user to see orders"),
            "Edit order"                                                            => _T("Edit order"),
            "allowes user to write and edit comments"                               => _T("allowes user to write and edit comments"),
            "Generate single order"                                                 => _T("Generate single order"),
            "allowes user to generate list with single orders"                      => _T("allowes user to generate list with single orders"),
            "Generate orders list"                                                  => _T("Generate orders list"),
            "allowes user to generate list with orders"                             => _T("allowes user to generate list with orders"),
            "Display more than one adrees"                                          => _T("Display more than one adrees"),
            "allowes user to see this information"                                  => _T("allowes user to see this information"),
            "Messages Mail"                                                         => _T("Messages / Mail"),
            "Clients"                                                               => _T("Clients"),
            "allowes user to send autogenerated mails to clients"                   => _T("allowes user to send autogenerated mails to clients"),
            "Departments"                                                           => _T("Departments"),
            "allowes user to send message to all users from selected department"    => _T("allowes user to send message to all users from selected department"),
            "Save"                                                                  => _T("Save"),
            "Logs"                                                                  => _T("Logs"),
            "allowes user to see available items"                                   => _T("allowes user to see logs"),
            "Storehouse"                                                            => _T("Storehouse"),
            "Items"                                                                 => _T("Items"),
            "allowes user to see available items_products"                          => _T("allowes user to see available items/products"),
        ]);
        
        //jQuery
        $jq = "
            $('#save').click(function() {
                //Load spinner
                load.show();
            
                //History
                var history_invoices_history = $('input[name=invoices_histoy]:checked', '#invoices_history').val();
                //var history_departments      = $('input[name=departments]:checked', '#departments_history').val();
                //var history_orders           = $('input[name=orders]:checked', '#orders_history').val();
                var history_logs             = $('input[name=logs_history]:checked', '#logs_history').val();

                //Storehouse
                var storehouse_items = $('input[name=items]:checked', '#storehouse_items').val();
                
                //Invoices
                var invoices_show_orders    = $('input[name=show_orders]:checked', '#invoices_show_orders').val();
                var invoices_generate       = $('input[name=generate]:checked', '#invoices_generate').val();
                
                //Orders
                var orders_upload_order          = $('input[name=upload_order]:checked', '#upload_orders').val();
                var orders_see_orders            = $('input[name=see_orders]:checked', '#see_orders').val();
                var orders_edit_order            = $('input[name=edit_order]:checked', '#edit_order').val();
                var orders_generate_single_order = $('input[name=generate_single_order]:checked', '#generate_order').val();
                var orders_generate_orders_list  = $('input[name=generate_orders_list]:checked', '#generate_orders').val();
                var orders_display_adress_more   = $('input[name=display_adress_more]:checked', '#display_adress_more').val();
                var orders_upload_customers      = $('input[name=upload_customer]:checked', '#upload_customers').val();
                
                //Messages
                var messages_clients     = $('input[name=clients]:checked', '#messages_clients').val();
                var messages_departments = $('input[name=departments]:checked', '#messages_departments').val();
                
                //post
                $.post('/permisions/set_permissions', {
                    history_invoices_history     : history_invoices_history,
                    //history_departments          : history_departments,
                    //history_orders               : history_orders,
                    history_logs                 : history_logs,
                    storehouse_items             : storehouse_items,
                    invoices_show_orders         : invoices_show_orders,
                    invoices_generate            : invoices_generate,
                    orders_upload_order          : orders_upload_order,
                    orders_upload_customers      : orders_upload_customers,
                    orders_see_orders            : orders_see_orders,
                    orders_edit_order            : orders_edit_order,
                    orders_generate_single_order : orders_generate_single_order,
                    orders_generate_orders_list  : orders_generate_orders_list,
                    orders_display_adress_more   : orders_display_adress_more,
                    messages_clients             : messages_clients,
                    messages_departments         : messages_departments,
                    user_id                      : '" . $slug . "'
                }, function(data) {
                    load.hide();
                    modal(data.title, data.body);
                }, 'json');
            });
        ";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("permisions/user_view");
    }
    
    /**
     * Set permissions
     * @author realdark <me@borislazarov.com> on 20 Jan 2015
     * @return void
     */
    public function setPermissionsAjax() {
        $userId = $_POST['user_id'];

        //Logger
        Log::logMe("Added new permissions");
        
        foreach ($_POST as $key => $value) {
            if ($key != "user_id") {
                Permission::updatePermission($key, $value, $userId);
            }
        }
        
        //Print msg
        Util::modal(true, _T("Success"), _T("Permissions was updated!"));
    }
    
    /**
     * Display permission page
     * @author realdark & SBYDev on 15 Jan 2015
     * @return void
     */
    public function displayDepartamentsTable() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //If is admin
        Authentication::isAdmin("exit");

        //Logger
        Log::logMe("Saw department table");
        
        //Track last action
        User::trackLastAction();
        
        $objDepartment = new Department();
        $arrDepartment = $objDepartment->fetchDepartments();
        
        foreach($arrDepartment as $key => $value) {
            $arrUsers = Department::fetchUsersByDepartment($value['id']);
            $arrDepartment[$key]['users'] = $arrUsers;
            //foreach($arrUsers as $user) {
            //    $arrDepartment[$key]['users'][] = $user['name'];
            //}
            
        }
        
        //\helpers\util::dbug($arrDepartment);
        
        //Add information in template
        $this->view->addContent([
            "title"            => _T("Display Departments"),
            "Show departments" => _T("Show departments"),
            "Home"             => _T("Home"),
            "User rights"      => _T("User rights"),
            "Name"             => _T("Name"),
            "Users"            => _T("Users"),
            "Actions"          => _T("Actions"),
            "departments_info" => $arrDepartment,
            "View"             => _T("View")
        ]);
        
        //jQuery
        $jq = "";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("permisions/departments_table");
    }
    
    /**
     * Display permission page
     * @author realdark & SBYDev on 15 Jan 2015
     * @return void
     */
    public function displayDepartamentView($slug) {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //If is admin
        Authentication::isAdmin("exit");
        
        //Track last action
        User::trackLastAction();
        
        //Fetch Permissions
        $objPermission = new Permission();
        $arrPermissions = $objPermission->fetchDepartmentPermissions($slug);
        //\helpers\util::dbug($arrPermissions);
        
        foreach ($arrPermissions as $permission) {
            switch ($permission['name']) {
                case "edit_order":
                    if ($permission['view'] == 1 && ($permission['edit'] == 0)) {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_view", "checked");
                    } else {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_edit", "checked");
                    }
                    break;
                default:
                    if ($permission['permission'] == 0) {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_none", "checked");
                    } else {
                        $this->view->addContent("c_" . $permission['main_department'] . "_" . $permission['name'] . "_view", "checked");
                    }
                    break;
            }
        }
        
        //Add information in template
        $this->view->addContent([
            "title"                                                              => _T("Display Department"),
            "User rights"                                                        => _T("User rights"),
            "Show users"                                                         => _T("Show users"),
            "Department"                                                         => _T("Department"),
            "History"                                                            => _T("History"),
            "Invoices history"                                                   => _T("Invoices history"),
            "allowes user to see created invoices"                               => _T("allowes user to see created invoices"),
            "No"                                                                 => _T("No"),
            "View"                                                               => _T("Views"),
            "Yes"                                                                => _T("Yes"),
            "View & Edit"                                                        => _T("View & Edit"),
            "Departments"                                                        => _T("Departments"),
            "allowes user to see actions of users in departments"                => _T("allowes user to see actions of users in departments"),
            "Orders"                                                             => _T("Orders"),
            "allowes users to see the actions of users for orders"               => _T("allowes users to see the actions of users for orders"),
            "Invoices"                                                           => _T("Invoices"),
            "Show orders"                                                        => _T("Show orders"),
            "allowes user to see orders"                                         => _T("allowes user to see orders"),
            "Generate"                                                           => _T("Generate"),
            "allowes user to generate invoices"                                  => _T("allowes user to generate invoices"),
            "Orders"                                                             => _T("Orders"),
            "Upload orders"                                                      => _T("Upload orders"),
            "allowes user to upload new orders"                                  => _T("allowes user to upload new orders"),
            "Upload customers"                                                   => _T("Upload customers"),
            "allowes user to upload new customers"                               => _T("allowes user to upload new customers"),
            "See orders"                                                         => _T("See orders"),
            "allowes user to see orders"                                         => _T("allowes user to see orders"),
            "Edit order"                                                         => _T("Edit order"),
            "allowes user to write and edit comments"                            => _T("allowes user to write and edit comments"),
            "Generate single order"                                              => _T("Generate single order"),
            "allowes user to generate list with single orders"                   => _T("allowes user to generate list with single orders"),
            "Generate orders list"                                               => _T("Generate orders list"),
            "allowes user to generate list with orders"                          => _T("allowes user to generate list with orders"),
            "Display more than one adrees"                                       => _T("Display more than one adrees"),
            "allowes user to see this information"                               => _T("allowes user to see this information"),
            "Messages Mail"                                                      => _T("Messages / Mail"),
            "Clients"                                                            => _T("Clients"),
            "allowes user to send autogenerated mails to clients"                => _T("allowes user to send autogenerated mails to clients"),
            "Departments"                                                        => _T("Departments"),
            "allowes user to send message to all users from selected department" => _T("allowes user to send message to all users from selected department"),
            "Save"                                                               => _T("Save"),
            "Logs"                                                               => _T("Logs"),
            "allowes user to see available items"                                => _T("allowes user to see logs"),
            "Storehouse"                                                         => _T("Storehouse"),
            "Items"                                                              => _T("Items"),
            "allowes user to see available items_products"                       => _T("allowes user to see available items/products"),
        ]);
        
        //jQuery
        $jq = "
            $('#save').click(function() {
                //Load spinner
                load.show();
            
                //History
                var history_invoices_history = $('input[name=invoices_histoy]:checked', '#invoices_history').val();
                //var history_departments      = $('input[name=departments]:checked', '#departments_history').val();
                //var history_orders           = $('input[name=orders]:checked', '#orders_history').val();
                var history_logs             = $('input[name=logs_history]:checked', '#logs_history').val();

                //Storehouse
                var storehouse_items = $('input[name=items]:checked', '#storehouse_items').val();
                
                //Invoices
                var invoices_show_orders    = $('input[name=show_orders]:checked', '#invoices_show_orders').val();
                var invoices_generate       = $('input[name=generate]:checked', '#invoices_generate').val();
                
                //Orders
                var orders_upload_order          = $('input[name=upload_order]:checked', '#upload_orders').val();
                var orders_see_orders            = $('input[name=see_orders]:checked', '#see_orders').val();
                var orders_edit_order            = $('input[name=edit_order]:checked', '#edit_order').val();
                var orders_generate_single_order = $('input[name=generate_single_order]:checked', '#generate_order').val();
                var orders_generate_orders_list  = $('input[name=generate_orders_list]:checked', '#generate_orders').val();
                var orders_display_adress_more   = $('input[name=display_adress_more]:checked', '#display_adress_more').val();
                var orders_upload_customers      = $('input[name=upload_customer]:checked', '#upload_customers').val();
                
                //Messages
                var messages_clients     = $('input[name=clients]:checked', '#messages_clients').val();
                var messages_departments = $('input[name=departments]:checked', '#messages_departments').val();
                
                //post
                $.post('/permisions/department_ajax', {
                    history_invoices_history     : history_invoices_history,
                    //history_departments          : history_departments,
                    //history_orders               : history_orders,
                    history_logs                 : history_logs,
                    storehouse_items             : storehouse_items,
                    invoices_show_orders         : invoices_show_orders,
                    invoices_generate            : invoices_generate,
                    orders_upload_order          : orders_upload_order,
                    orders_upload_customers      : orders_upload_customers,
                    orders_see_orders            : orders_see_orders,
                    orders_edit_order            : orders_edit_order,
                    orders_generate_single_order : orders_generate_single_order,
                    orders_generate_orders_list  : orders_generate_orders_list,
                    orders_display_adress_more   : orders_display_adress_more,
                    messages_clients             : messages_clients,
                    messages_departments         : messages_departments,
                    department_id                : '" . $slug . "'
                }, function(data) {
                    load.hide();
                    modal(data.title, data.body);
                }, 'json');
            });
        ";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("permisions/department_view");
    }
    
    /** Set default values for department
     * @author realdark <me@borislazarov.com> on 28 Jan 2015
     * @return json
     */
    public function displayDepartamentAjax() {
        $departmentId = $_POST['department_id'];
        
        foreach ($_POST as $key => $value) {
            if ($key != "department_id") {
                Permission::updateFepratmentPermission($key, $value, $departmentId);
            }
        }
        
        //Print msg
        Util::modal(true, _T("Success"), _T("Permissions was updated!"));
    }
    
    /**
     * Create department
     * @author realdark <me@borislazarov.com> on 29 Jan 2015
     * @return void
     */
    public function createDepartment() {
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //If is admin
        Authentication::isAdmin("exit");
        
        //Track last action
        User::trackLastAction();
        
        //Add information in template
        $this->view->addContent([
            "title"                                                                 => _T("Display User"),
            "Home"                                                                  => _T("Home"),
            "User rights"                                                           => _T("User rights"),
            "Show users"                                                            => _T("Show users"),
            "Create Department"                                                     => _T("Create Department"),
            "History"                                                               => _T("History"),
            "Invoices history"                                                      => _T("Invoices history"),
            "allowes user to see created invoices"                                  => _T("allowes user to see created invoices"),
            "No"                                                                    => _T("No"),
            "View"                                                                  => _T("Views"),
            "Yes"                                                                   => _T("Yes"),
            "View & Edit"                                                           => _T("View & Edit"),
            "Departments"                                                           => _T("Departments"),
            "Department name"                                                       => _T("Department name:"),
            "Enter department name"                                                 => _T("Enter department name"),
            "allowes user to see actions of users in departments"                   => _T("allowes user to see actions of users in departments"),
            "Orders"                                                                => _T("Orders"),
            "allowes users to see the actions of users for orders"                  => _T("allowes users to see the actions of users for orders"),
            "Invoices"                                                              => _T("Invoices"),
            "Show orders"                                                           => _T("Show orders"),
            "allowes user to see orders"                                            => _T("allowes user to see orders"),
            "Generate"                                                              => _T("Generate"),
            "allowes user to generate invoices"                                     => _T("allowes user to generate invoices"),
            "Orders"                                                                => _T("Orders"),
            "Upload orders"                                                         => _T("Upload orders"),
            "allowes user to upload new orders"                                     => _T("allowes user to upload new orders"),
            "Upload customers"                                                      => _T("Upload customers"),
            "allowes user to upload new customers"                                  => _T("allowes user to upload new customers"),
            "See orders"                                                            => _T("See orders"),
            "allowes user to see orders"                                            => _T("allowes user to see orders"),
            "Edit order"                                                            => _T("Edit order"),
            "allowes user to write and edit comments"                               => _T("allowes user to write and edit comments"),
            "Generate single order"                                                 => _T("Generate single order"),
            "allowes user to generate list with single orders"                      => _T("allowes user to generate list with single orders"),
            "Generate orders list"                                                  => _T("Generate orders list"),
            "allowes user to generate list with orders"                             => _T("allowes user to generate list with orders"),
            "Display more than one adrees"                                          => _T("Display more than one adrees"),
            "allowes user to see this information"                                  => _T("allowes user to see this information"),
            "Messages Mail"                                                         => _T("Messages / Mail"),
            "Clients"                                                               => _T("Clients"),
            "allowes user to send autogenerated mails to clients"                   => _T("allowes user to send autogenerated mails to clients"),
            "Departments"                                                           => _T("Departments"),
            "allowes user to send message to all users from selected department"    => _T("allowes user to send message to all users from selected department"),
            "Save"                                                                  => _T("Save"),
            "Logs"                                                               => _T("Logs"),
            "allowes user to see available items"                                => _T("allowes user to see logs"),
            "Storehouse"                                                         => _T("Storehouse"),
            "Items"                                                              => _T("Items"),
            "allowes user to see available items_products"                       => _T("allowes user to see available items/products"),
        ]);
        
        //jQuery
        $jq = "
            $('#save').click(function() {
                //Load spinner
                load.show();
            
                //History
                var history_invoices_history = $('input[name=invoices_histoy]:checked', '#invoices_history').val();
                //var history_departments      = $('input[name=departments]:checked', '#departments_history').val();
                //var history_orders           = $('input[name=orders]:checked', '#orders_history').val();
                var history_logs             = $('input[name=logs_history]:checked', '#logs_history').val();

                //Storehouse
                var storehouse_items = $('input[name=items]:checked', '#storehouse_items').val();
                
                //Invoices
                var invoices_show_orders    = $('input[name=show_orders]:checked', '#invoices_show_orders').val();
                var invoices_generate       = $('input[name=generate]:checked', '#invoices_generate').val();
                
                //Orders
                var orders_upload_order          = $('input[name=upload_order]:checked', '#upload_orders').val();
                var orders_see_orders            = $('input[name=see_orders]:checked', '#see_orders').val();
                var orders_edit_order            = $('input[name=edit_order]:checked', '#edit_order').val();
                var orders_generate_single_order = $('input[name=generate_single_order]:checked', '#generate_order').val();
                var orders_generate_orders_list  = $('input[name=generate_orders_list]:checked', '#generate_orders').val();
                var orders_display_adress_more   = $('input[name=display_adress_more]:checked', '#display_adress_more').val();
                var orders_upload_customers      = $('input[name=upload_customer]:checked', '#upload_customers').val();
                
                //Messages
                var messages_clients     = $('input[name=clients]:checked', '#messages_clients').val();
                var messages_departments = $('input[name=departments]:checked', '#messages_departments').val();
                
                //Department create
                var department_name = $('#department_name_create').val();
                
                //post
                $.post('/permisions/department_create_ajax', {
                    history_invoices_history     : history_invoices_history,
                    //history_departments          : history_departments,
                    //history_orders               : history_orders,
                    history_logs                 : history_logs,
                    storehouse_items             : storehouse_items,
                    invoices_show_orders         : invoices_show_orders,
                    invoices_generate            : invoices_generate,
                    orders_upload_order          : orders_upload_order,
                    orders_upload_customers      : orders_upload_customers,
                    orders_see_orders            : orders_see_orders,
                    orders_edit_order            : orders_edit_order,
                    orders_generate_single_order : orders_generate_single_order,
                    orders_generate_orders_list  : orders_generate_orders_list,
                    orders_display_adress_more   : orders_display_adress_more,
                    messages_clients             : messages_clients,
                    messages_departments         : messages_departments,
                    department_name              : department_name
                }, function(data) {
                    load.hide();
                    modal(data.title, data.body);
                }, 'json');
            });
        ";
        
        //Add information in template
        $this->view->addContent("jq", $jq);
        
        //Render template
        $this->view->loadPage("permisions/department_create");
    }
    
    /**
     * Create department ajax
     * @author realdark <me@borislazarov.com> on 29 Jan 2015
     * @return json
     */
    public function createDepartmentAjax() {
        //Track last action
        User::trackLastAction();
        
        $departmentName = $_POST['department_name'];

        //Logger
        Log::logMe("Created new department with name " . $departmentName);
        
        $objDepartment = new Department();
        $objDepartment->setName($departmentName);
        
        try {
            $departmentId = $objDepartment->save();
        } catch (\Exception $e) {
            \core\logger::exception_handler($e);
        }
        
        foreach ($_POST as $key => $value) {
            if ($key != "department_id") {
                Permission::createDepartment($key, $value, $departmentId);
            }
        }
        
        //Print msg
        Util::modal(true, _T("Success"), _T("New department was created!"));
    }
}