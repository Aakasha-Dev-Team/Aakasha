<?php
if(file_exists('vendor/autoload.php')){
	require 'vendor/autoload.php';
} else {
	echo "<h1>Please install via composer.json</h1>";
	echo "<p>Install Composer instructions: <a href='https://getcomposer.org/doc/00-intro.md#globally'>https://getcomposer.org/doc/00-intro.md#globally</a></p>";
	echo "<p>Once composer is installed navigate to the working directory in your terminal/command promt and enter 'composer install'</p>";
	exit;
}

if (!is_readable('app/core/config.php')) {
	die('No config.php found, configure and rename config.example.php to config.php in app/core.');
}

/*
 *---------------------------------------------------------------
 * APPLICATION ENVIRONMENT
 *---------------------------------------------------------------
 *
 * You can load different configurations depending on your
 * current environment. Setting the environment also influences
 * things like logging and error reporting.
 *
 * This can be set to anything, but default usage is:
 *
 *     development
 *     production
 *
 * NOTE: If you change these, also change the error_reporting() code below
 *
 */
	define('ENVIRONMENT', 'development');
/*
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 *
 * Different environments will require different levels of error reporting.
 * By default development will show errors but production will hide them.
 */

if (defined('ENVIRONMENT')){

	switch (ENVIRONMENT){
		case 'development':
			error_reporting(E_ALL);
		break;

		case 'production':
			error_reporting(0);
		break;

		default:
			exit('The application environment is not set correctly.');
	}

}

//initiate config
new \core\config();

//create alias for Router
use \core\router as Router,
    \helpers\url as Url;

//define routes
Router::any('', '\controllers\MainController@index');
Router::any('orders/index', '\controllers\OrderController@index');
Router::any('orders/upload_ajax', '\controllers\OrderController@uploadFileAjax');
Router::any('orders/generate_invoice/(:any)', '\controllers\OrderController@generateInvoice');
Router::any('orders/display_orders', '\controllers\OrderController@displayOrders');
Router::any('orders/fetch_orders_ajax', '\controllers\OrderController@fetchOrdersAjax');
Router::any('orders/fetch_order_ajax', '\controllers\OrderController@fetchOrderAjax');
Router::any('orders/check_order_ajax', '\controllers\OrderController@checkOrderAjax');
Router::any('orders/add_comment_ajax', '\controllers\OrderController@addCommentAjax');
Router::any('orders/add_newadress_ajax', '\controllers\OrderController@addNewAdressAjax');
Router::any('orders/generate_invoice_ajax', '\controllers\OrderController@generateInvoiceAjax');
Router::any('orders/generate_order_ajax', '\controllers\OrderController@generateOrderAjax');
Router::any('orders/calculate_shipping_ajax', '\controllers\OrderController@calculateShippingAjax');
Router::any('orders/generate_order/(:any)', '\controllers\OrderController@generateOrder');
Router::any('orders/generate_orders', '\controllers\OrderController@generateOrders');
Router::any('orders/generate_orders_ajax', '\controllers\OrderController@generateOrdersAjax');
Router::any('orders/set_row_height_ajax', '\controllers\OrderController@setOrderEmptyRowHeightAjax');
Router::any('orders/change_product_status_ajax', '\controllers\OrderController@productProgress');
Router::any('orders/set_product_controls_ajax', '\controllers\OrderController@productControls');
Router::any('orders/history/(:num)', '\controllers\OrderController@orderHistory');
Router::any('orders/feedback/(:num)', '\controllers\OrderController@orderFeedback');
Router::any('orders/feedback_ajax', '\controllers\OrderController@orderFeedbackAjax');
Router::any('orders/order_checked_by_ajax', '\controllers\OrderController@orderCheckedByAjax');
Router::any('emails/upload_emails', '\controllers\EmailController@uploadEmails');
Router::any('emails/upload_emails_ajax', '\controllers\EmailController@uploadEmailsAjax');
Router::any('user/sign_in', '\controllers\AuthenticationController@index');
Router::any('user/sign_in_ajax', '\controllers\AuthenticationController@signInAjax');
Router::any('user/log_out', '\controllers\AuthenticationController@logOut');
Router::any('user/show_profile', '\controllers\UserController@showProfile');
Router::any('user/add_edit_profile/(:num)', '\controllers\UserController@showAddEditProfile');
Router::any('user/add_edit_profile/(:num)', '\controllers\UserController@showAddEditProfile');
Router::any('user/add_edit_user_ajax', '\controllers\UserController@addEditUserAjax');
Router::any('user/bookmarks', '\controllers\UserController@bookmarks');
Router::any('user/bookmarks_ajax', '\controllers\UserController@bookmarksAjax');
Router::any('history/display_invoices', '\controllers\HistoryController@displayInvoices');
Router::any('history/display_invoices_ajax', '\controllers\HistoryController@displayInvoicesAjax');
Router::any('history/fetch_invoice_ajax', '\controllers\HistoryController@fetchInvoiceAjax');
Router::any('permisions/users_table', '\controllers\PermissionController@displayUsersTable');
Router::any('permisions/users_table_ajax', '\controllers\PermissionController@usersTableAjax');
Router::any('permisions/set_permissions', '\controllers\PermissionController@setPermissionsAjax');
Router::any('permisions/user_view/(:num)', '\controllers\PermissionController@displayUserView');
Router::any('permisions/departments_table', '\controllers\PermissionController@displayDepartamentsTable');
Router::any('permisions/department_view/(:num)', '\controllers\PermissionController@displayDepartamentView');
Router::any('permisions/department_ajax', '\controllers\PermissionController@displayDepartamentAjax');
Router::any('permisions/department_create', '\controllers\PermissionController@createDepartment');
Router::any('permisions/department_create_ajax', '\controllers\PermissionController@createDepartmentAjax');
Router::any('storehouse/display_items', '\controllers\StoreHouseController@displayItems');
Router::any('storehouse/new_item_save', '\controllers\StoreHouseController@createSave');
Router::any('storehouse/existing_item_delete', '\controllers\StoreHouseController@doDelete');
Router::any('storehouse/existing_item_edit', '\controllers\StoreHouseController@doEdit');
Router::any('storehouse/edit/(:num)', '\controllers\StoreHouseController@edit');
Router::any('storehouse/stats/(:num)', '\controllers\StoreHouseController@stats');
Router::any('reports/new', '\controllers\ReportController@newReport');
Router::any('reports/generate', '\controllers\ReportController@generateReport');
Router::any('reports/save', '\controllers\ReportController@saveReport');
Router::any('reports/saved', '\controllers\ReportController@viewReports');
Router::any('reports/delete/(:num)', '\controllers\ReportController@deleteReport');
Router::any('reports/details/(:num)', '\controllers\ReportController@showReport');
Router::any('reports/edit/(:num)', '\controllers\ReportController@editReport');

//Log
Router::any('history/log', '\controllers\LogController@index');
Router::any('history/fetch_logs_ajax', '\controllers\LogController@fetchLogsAjax');

//chat
Router::any('chat/chatheartbeat', '\controllers\ChatController@chatHeartbeat');
Router::any('chat/sendchat', '\controllers\ChatController@sendChat');
Router::any('chat/closechat', '\controllers\ChatController@closeChat');
Router::any('chat/startchatsession', '\controllers\ChatController@startChatSession');
Router::any('chat/chatfetchstatus', '\controllers\ChatController@fetchStatus');
Router::any('chat/chat_home/(:any)', '\controllers\ChatController@chatHome');
Router::any('chat/send_message_action', '\controllers\ChatController@sendMessageAction');
Router::any('chat/recieve_messages_action', '\controllers\ChatController@recieveMessagesAction');
Router::any('chat/recent_users_chat', '\controllers\ChatController@recentUsersChat');

//if no route found
Router::error('\core\error@index');

//turn on old style routing
Router::$fallback = true;

//execute matched routes
Router::dispatch();
