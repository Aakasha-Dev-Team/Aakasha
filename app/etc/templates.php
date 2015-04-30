<?php
$sessionId = \helpers\Session::get('user_id');

$objUser  = new \models\User($sessionId, ['name', 'avatar']);
$username = $objUser->getName();
$avatar   = $objUser->getAvatar();

//User permissions
$permissionsOrders   = \helpers\Globals::get("orders_permissions");
$permissionsInvoices = \helpers\Globals::get("invoices_permissions");
$permissionsHistory  = \helpers\Globals::get("history_permissions");
$permissionsStorehouse  = \helpers\Globals::get("storehouse_permissions");

//users chat
$users = \models\User::fetchUsersChat();

//bookmarks
$bookmarks = \models\Bookmark::fetchBookmarks();

//Set vars
$vars = [
    "default" => [
            "template" => [
                "site title"              => SITETITLE,
                "Toggle navigation"       => _T("Toggle navigation"),
                "Aakasha Internal System" => _T("Aakasha Internal System"),
                "Advanced Search"         => _T("Advanced Search"),
                "Reports"                 => _T("Reports"),
                "Invoices History"        => _T("Invoices History"),
                "Orders"                  => _T("Orders"),
                "Generate"                => _T("Generate"),
                "Items"                   => _T("Items"),
                "Price List"              => _T("Price List"),
                "LookBook"                => _T("LookBook (catalog)"),
                "Item details"            => _T("Item details"),
                "Production"              => _T("Production"),
                "Order cycle"             => _T("Order cycle"),
                "History"                 => _T("History"),
                "Search"                  => _T("Search"),
                "Administration"          => _T("Administration"),
                "User privilegues"        => _T("User privilegues"),
                "Test area"               => _T("Test area"),
                "Parameters"              => _T("Parameters"),
                "Roles"                   => _T("Roles"),
                "Close"                   => _T("Close"),
                "Logout"                  => _T("Logout"),
                "users_online"            => $users['online'],
                "users_offline"           => $users['offline']
            ],
            "error" => [
                'title'                                                                                                                => '404',
                'The page you were looking for could not be found'                                                                     => _T("The page you were looking for could not be found"),
                "This could be the result of the page being removed, the name being changed or the page being temporarily unavailable" => _T("This could be the result of the page being removed, the name being changed or the page being temporarily unavailable"),
                "Troubleshooting"                                                                                                      => _T("Troubleshooting"),
                "If you spelled the URL manually, double check the spelling"                                                           => _T("If you spelled the URL manually, double check the spelling"),
                "Go to our website's home page, and navigate to the content in question"                                               => _T("Go to our website's home page, and navigate to the content in question")
            ]
        ],
    "sbydev_realdark" => [
            "template" => [
                "site title"              => SITETITLE,
                "Toggle navigation"       => _T("Toggle navigation"),
                "Aakasha Internal System" => _T("Aakasha Internal System"),
                "Advanced Search"         => _T("Advanced Search"),
                "Reports"                 => _T("Reports"),
                "Invoices History"        => _T("Invoices History"),
                "Orders"                  => _T("Orders"),
                "Generate"                => _T("Generate"),
                "Items"                   => _T("Items"),
                "Price List"              => _T("Price List"),
                "LookBook"                => _T("LookBook (catalog)"),
                "Item details"            => _T("Item details"),
                "Production"              => _T("Production"),
                "Order cycle"             => _T("Order cycle"),
                "History"                 => _T("History"),
                "Search"                  => _T("Search"),
                "Administration"          => _T("Administration"),
                "User privilegues"        => _T("User privilegues"),
                "Test area"               => _T("Test area"),
                "Parameters"              => _T("Parameters"),
                "Roles"                   => _T("Roles"),
                "Close"                   => _T("Close"),
                "Logout"                  => _T("Logout"),
                "Users"                   => _T("Users"),
                "Production"              => _T("Production"),
                "Departments"             => _T("Departments"),
                "Packaging"               => _T("Packaging"),
                "Sales"                   => _T("Sales"),
                "In production"           => _T("In production"),
                "Closed"                  => _T("Closed"),
                "All"                     => _T("All"),
                "Upload orders"           => _T("Upload orders"),
                "Show orders"             => _T("Show orders"),
                "Orders list"             => _T("Orders list"),
                "Single order"            => _T("Single order"),
                "Generation"              => _T("Generation"),
                "Invoices"                => _T("Invoices"),
                "Invoice generation"      => _T("Invoice generation"),
                "Generate invoice"        => _T("Generate invoice"),
                "Select type"             => _T("Select type"),
                "Select status"           => _T("Select status"),
                "Permisions"              => _T("Permisions"),
                "Show users"              => _T("Show users"),
                "Create user"             => _T("Create user"),
                "Show departments"        => _T("Show departments"),
                "Create department"       => _T("Create department"),
                "User rights"             => _T("User rights"),
                "My Profile"              => _T("My Profile"),
                "Hi"                      => _T("Hi"),
                "Search Menu"             => _T("Search Menu"),
                "Upload customers"        => _T("Upload customers"),
                "username"                => $username,
                "orders_permissions"      => $permissionsOrders,
                "invoices_permissions"    => $permissionsInvoices,
                "history_permissions"     => $permissionsHistory,
                "storehouse_permissions"  => $permissionsStorehouse,
                "is_admin"                => \models\Authentication::isAdmin(),
                "users_online"            => $users['online'],
                "users_offline"           => $users['offline'],
                "avatar"                  => $avatar,
                "Bookmarks"               => _T("Bookmarks"),
                "Account Setting"         => _T("Account Setting"),
                "bookmarks_data"          => $bookmarks,
                "Storehouse"              => _T("Storehouse"),
                'Logs'                    => _T("Logs"),
                'user_id'                 => $sessionId,
                'display_reports'         => _T("Show"),
                'new_report'              => _T("New")
            ],
            "error" => [
                'title'                                                                                                                => '404',
                'The page you were looking for could not be found'                                                                     => _T("The page you were looking for could not be found"),
                "This could be the result of the page being removed, the name being changed or the page being temporarily unavailable" => _T("This could be the result of the page being removed, the name being changed or the page being temporarily unavailable"),
                "Troubleshooting"                                                                                                      => _T("Troubleshooting"),
                "If you spelled the URL manually, double check the spelling"                                                           => _T("If you spelled the URL manually, double check the spelling"),
                "Go to our website's home page, and navigate to the content in question"                                               => _T("Go to our website's home page, and navigate to the content in question")
            ]
        ]
];

//Set vars in globals
\helpers\globals::set('template_vars', $vars);
?>