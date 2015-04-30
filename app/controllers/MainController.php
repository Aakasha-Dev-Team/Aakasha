<?php namespace controllers;
use core\view as View,
    helpers\Session as Session,
    helpers\Globals as Globals,
    models\Authentication as Authentication,
    models\User as User,
    models\Order as Order;
use models\ShoppingCart;

/**
 * Main controller
 * @author
 */
class MainController extends \core\controller {

    public function __construct() {
        parent::__construct();
    }

    /**
    * Home page
    * @author realdark <me@borislazarov.com>
    */ 
    public function index() {
        
        //If user is not logged send him to loggin page
        Authentication::chechAuthentication("exit");
        
        //Track last action
        User::trackLastAction();
        
        //\helpers\util::dbug($_SESSION);
        
        //permissions
        $permission = Globals::get("orders_permissions");
        
        if ($permission['display_adress_more']['permission'] == 1) {
            $moreThanOneAdress = Order::displayAdressMore();
        } else {
            $moreThanOneAdress = "";
        }

        //time limit
        $objOrder = new Order();
        $orders = $objOrder->fetchActiveOrders();

        foreach($orders as $key => $order) {
            $orders[$key]['time_limit'] = $this->getElapsedDays($order['scheduled_to_ship_date']);
        }
        
        $sessionId = Session::get('user_id');
        
        $objUser     = new User($sessionId);
        $last_signin = $objUser->getLastSigIn();
        
        $dateTime = new \DateTime($last_signin);
        $date     = $dateTime->format("F j, Y, g:i a");
        
        //Add information in template
        $this->view->addContent([
            "title"                                                            => "Home page",
            "day_time_last_signin"                                             => $date,
            "users_with_more_one_adress"                                       => $moreThanOneAdress,
            "with order"                                                       => _T("with order"),
            "has more than one address"                                        => _T("has more than one address"),
            "Please take in considuration that shipping adresses are diferent" => _T("Please take in considuration that shipping adresses are diferent"),
            "Orders overview"                                                  => _T("ORDERS OVERVIEW"),
            "home"                                                             => _T("Home"),
            "dashboard"                                                        => _T("Dashboard"),
            "username"                                                         => $objUser->getName(),
            "orders with more than one address"                                => _T("Orders with more than one address"),
            "orders progress"                                                  => _T("Orders Progress"),
            "time_limit"                                                       => $orders,
            "purchased items"                                                  => _T("Purchased items"),
            "count_items"                                                      => ShoppingCart::purchasedItems(),
            "sku"                                                              => _T("Sku"),
            "name_en"                                                          => _T("Name EN"),
            "quantity"                                                         => _T("Quantity"),
            "size"                                                             => _T("Size"),
            "color"                                                            => _T("Color")
        ]);
        
        //last sign in
        $dateTime = new \DateTime();
        $date     = $dateTime->format("Y-m-d H:i");
        
        $objUser->setLastSigIn($date);
        
        try {
            $objUser->save();
        } catch (\Exception $e) {
            \core\logger::exception_handler($e);
        }

        //Render template
        $this->view->loadPage("home_page");
    }

    /**
     * Change item color
     * @author realdark <me@borislazarov.com>
     * @param $lastDayToShip
     * @internal param $orderDate
     * @return str
     */
    private function getElapsedDays($lastDayToShip) {
        $order  = new \DateTime(date('Y-m-d'));
        $toShip = new \DateTime($lastDayToShip);
        //$toShip->modify('+1 day');

        $dDiff = $order->diff($toShip);

        if ($order->getTimestamp() > $toShip->getTimestamp()) {
            return "Expired.";
        }

        return $dDiff->days . " days to time limit.";
    }
}