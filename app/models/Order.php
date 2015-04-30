<?php namespace models;
use helpers\util as Util,
    libraries\excel\PHPExcel as PHPExcel,
    models\Customer as Customer,
    models\ShoppingCart as ShoppingCart;

/**
 * Order model
 * @author
 */
class Order extends \core\model {
    
    private $uploadedFile;
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Upload file
     * @author Bobi <me@borislazarov.com> on 6 Dec 2014
     * @return boolean
     */
    public function uploadFile($file) {
        $uploaddir     = ROOT_PATH . "uploads/orders/";
        $uploadfile    = $uploaddir . basename($_FILES[0]['name']);
        $fileExtension = explode(".", $_FILES[0]['name']);
        $allowedtypes  = ["xlsx", "xls"];
        
        if (in_array(end($fileExtension), $allowedtypes)) {
            if (move_uploaded_file($_FILES[0]['tmp_name'], $uploadfile)) {
                $result = true;
            } else {
                $result = false;
            }
        }
        
        $this->uploadedFile = new \StdClass;
        $this->uploadedFile->type = end($fileExtension);
        $this->uploadedFile->url  = $uploadfile;
        
        return $result;
    }
    
    /**
     * Import order from file to db
     * @author Bobi <me@borislazarov.com> on 6 Dec 2014
     * @return mixed
     */
    public function importOrderToDB() {
        require_once APP_PATH . 'libraries/excel/PHPExcel.php';
        
        $filename = $this->uploadedFile->url;
        
        $filetype    = \PHPExcel_IOFactory::identify($filename);
        $objReader   = \PHPExcel_IOFactory::createReader($filetype);
        $objReader->setReadDataOnly(true);  // set this if you don't need to write
        $objPHPExcel = $objReader->load($filename);
        
        foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
            $arrayData[] = $worksheet->toArray();
        }
        
        //orders id array
        //$arrOrders = [];
        
        foreach ($arrayData[0] as $key => $row) {
            if ($key == 0) {
                $titles = $row;
                
                //chech for proper alignment of the table
                $reqCols = [
                    "SalesDate&Order",
                    "Scheduled to ship date",
                    "Standard - Yes/No",
                    "Note from Buyer",
                    "Your private notes",
                    "Buyer",
                    "Address - full",
                    "Item Name",
                    "ItemBG",
                    "Quantity",
                    "Fabric",
                    "Color BG",
                    "Sizes - Fixed",
                    "ItemCode",
                    "Image 1",
                    "Image 2",
                    "Date Paid",
                    "Date Shipped"
                ];
                
                $msg = "";
                
                foreach ($reqCols as $cols) {
                    if (array_search($cols, $titles) === false) {
                        $msg     .= _T("Col `$cols` does not exist in the file! This col is required.") . "<br>";
                        $booError = true;
                    }
                }
                
                //if found a error send msg
                if ($booError == true) {
                    return $msg;
                }
            }
            
            if ($key > 0) {
                
                //chech for empty cells
                if (!empty($row[array_search('Item Name', $titles)])) {
                
                    //orders
                    $saleAndDate            = $row[array_search('SalesDate&Order', $titles)];
                    $saleAndDate            = explode(" ", $saleAndDate);
                    $sale                   = $saleAndDate[0];
                    $scheduled_to_ship_date = \PHPExcel_Shared_Date::ExcelToPHP($row[array_search('Scheduled to ship date', $titles)]);
                    $scheduled_to_ship_date = date('Y-m-d', $scheduled_to_ship_date);
                    $standart               = $row[array_search('Standard - Yes/No', $titles)] == "No" ? 0 : 1;
                    $note_from_bayer        = $row[array_search('Note from Buyer', $titles)];
                    $note_from_seller       = $row[array_search('Your private notes', $titles)];
                    //$date                   = \PHPExcel_Shared_Date::ExcelToPHP($sale[1]);
                    $dt                     = new \DateTime($saleAndDate[1]);
                    $date                   = $dt->format('Y-m-d');
                    
                    //new :: added on 12 Dec 2014
                    $paid_date    = \PHPExcel_Shared_Date::ExcelToPHP($row[array_search('Date Paid', $titles)]);
                    $paid_date    = date('Y-m-d', $paid_date);
                    
                    if (($row[array_search('Date Shipped', $titles)] != "(blank)") && !empty($row[array_search('Date Shipped', $titles)])) {
                        $shipped_date = \PHPExcel_Shared_Date::ExcelToPHP($row[array_search('Date Shipped', $titles)]);
                        $shipped_date = date('Y-m-d', $shipped_date);
                    } else {
                        $shipped_date = "0000-00-00 00:00:00";
                    }
                    
                    //customers
                    $fullName = $row[array_search('Buyer', $titles)];
                    $adress   = $row[array_search('Address - full', $titles)];
                    
                    //shopping cart
                    $nameEn   = $row[array_search('Item Name', $titles)];
                    $nameBg   = $row[array_search('ItemBG', $titles)];
                    $quantity = $row[array_search('Quantity', $titles)];
                    $fabric   = $row[array_search('Fabric', $titles)];
                    $color    = $row[array_search('Color BG', $titles)];
                    $size     = $row[array_search('Sizes - Fixed', $titles)];
                    $sku      = $row[array_search('ItemCode', $titles)];
                    $image_1  = $row[array_search('Image 1', $titles)];
                    $image_2  = $row[array_search('Image 2', $titles)];
                    
                    //If order exist
                    $order = self::for_table(PREFIX . 'orders')->where('sale', $sale)->find_one();
                    
                    if ($order == false) {
                        $this->setSale($sale);
                        $this->setScheduledToShipDate($scheduled_to_ship_date);
                        $this->setStandart($standart);
                        $this->setNoteFromBayer($note_from_bayer);
                        $this->setNoteFromSeller($note_from_seller);
                        
                        if (isset($paid_date)) {
                            $this->setPaidDate($paid_date);
                        }
                        
                        if (isset($shipped_date) && ($shipped_date != '(blank)')) {
                            $this->setShippedDate($shipped_date);
                        }
                        
                        $this->setOrderDate($date);
                        
                        try {
                            $orderId = $this->save();
                        } catch (\Exception $e) {
                            $this->logger->logEvent($e->getMessage());
                        }
                        
                        $objCustomer = new Customer();
                        $objCustomer->setOrderId($orderId);
                        $objCustomer->setFullName($fullName);
                        $objCustomer->setShippingAdress($adress);
                        
                        
                        try {
                            $objCustomer->save();
                        } catch (\Exception $e) {
                            $this->logger->logEvent($e->getMessage());
                        }
                        
                    //Get order id and update shipped and paid date
                    } else {
                        if (isset($paid_date)) {
                            $order->setPaidDate($paid_date);
                        }
                        
                        if (isset($shipped_date) && ($shipped_date != '(blank)')) {
                            $order->setShippedDate($shipped_date);
                        }
                        
                        try {
                            $order->save();
                        } catch (\Exception $e) {
                            $this->logger->logEvent($e->getMessage());
                        }
                        
                        $orderId = $order->getId();
                    }
                    
                    //Current time  
                    $dtNow    = new \DateTime();
                    $dt_now  = $dtNow->format('Y-m-d H:i:s');
                    
//                    if (array_search($sale, $arrOrders) === false) {
//                        $cart = self::for_table(PREFIX . 'shopping_cart')
//                            ->where('order_id', $orderId)
//                            ->delete_many();
//
//                        $arrOrders[] = $sale;
//                    }

                    //Detect if product exist in db
                    if (!empty($sku)) {
                        $objShoppingCart = new ShoppingCart(['order_id' => $orderId, 'sku' => $sku, 'name_en' => $nameEn, 'color' => $color, 'size' => $size], ['created_system']);
                    } else {
                        $objShoppingCart = new ShoppingCart(['order_id' => $orderId, 'name_en' => $nameEn], ['created_system']);
                    }

                    if ($objShoppingCart !== false) {
                        $created = new \DateTime($objShoppingCart->created_system);
                        $dDiff = $created->diff($dtNow);

                        $minutes = $dDiff->days * 24 * 60;
                        $minutes += $dDiff->h * 60;
                        $minutes += $dDiff->i;
                    } else {
                        $minutes = 0;
                    }

                    if ($minutes <= 1) {
                        //Create object
                        $objShoppingCart = new ShoppingCart();

                        $objShoppingCart->setOrderId($orderId);
                        $objShoppingCart->setNameEn($nameEn);
                        $objShoppingCart->setNameBg($nameBg);
                        $objShoppingCart->setQuantity($quantity);

                        if (isset($fabric)) {
                            $objShoppingCart->setFabric($fabric);
                        }

                        if (isset($color)) {
                            $objShoppingCart->setColor($color);
                        }

                        if (isset($size)) {
                            $objShoppingCart->setSize($size);
                        }

                        if (!empty($sku)) {
                            $objShoppingCart->setSku($sku);
                        } else {
                            $error[] = _T("Item `" . $nameEn . "` from order number " . $sale . " does not have SKU!");
                        }

                        $objShoppingCart->setImageOne($image_1);
                        $objShoppingCart->setImageTwo($image_2);

                        //set created date

                        $objShoppingCart->setCreatedSystem($dt_now);

                        try {
                            $objShoppingCart->save();
                        } catch (\Exception $e) {
                            $this->logger->logEvent($e->getMessage());
                        }
                    }
                    //
                }
                
            }
        }
        
        if ($error > 0) {
            $msg = "<br>";
            
            foreach ($error as $key => $value) {
                $msg .= "<p>" . $value . "</p>";
            }
            
            return $msg;
        } else {
            return true;
        }
        
    }
    
    /**
     * Fetch orders
     * @author Bobi <me@borislazarov.com> on 11 Dec 2014
     * @return array
     */
    public function fetchOrders($values, $type = "standart") {
        
        $dateTime = new \DateTime($values['to']);
        $dateTime->setTime(23, 59);
        $values['to'] = $dateTime->format('Y-m-d H:i:s');
        
        //order status
        if ($values['order_status'] == "all") {
            $where = NULL;
        } elseif ($values['order_status'] == "active") {
            $where = " AND shipped_date = '0000-00-00 00:00:00'";
        } else {
            $where = " AND shipped_date != '0000-00-00 00:00:00'";
        }
        
        //order starnart
        if ($values['order_standart'] == "all") {
            $where .= NULL;
        } elseif ($values['order_standart'] == "false") {
            $where .= " AND standart = 0";
        } else {
            $where .= " AND standart = 1";
        }
        
        switch ($type) {
            case "standart":
                $orders = self::for_table(PREFIX . 'orders')->raw_query("
                    SELECT id, sale, order_date, shipped_date, scheduled_to_ship_date FROM " . PREFIX . "orders WHERE order_date between '" . $values['from'] . "' and '" . $values['to'] . "'
                " . $where . " ORDER BY id DESC")->find_array();
                break;
            
            case "extended":
                $tmpOrders = self::for_table(PREFIX . 'orders')->raw_query("
                    SELECT o.id, o.sale, o.order_date, o.scheduled_to_ship_date, o.standart, o.note_from_bayer, o.note_from_seller, o.empty_row_height, o.new_adress, c.full_name, c.shipping_adress 
                    FROM " . PREFIX . "orders o 
                    INNER JOIN " . PREFIX . "customers c ON o.id = c.order_id 
                    WHERE o.order_date between '" . $values['from'] . "' and '" . $values['to'] . "'
                " . $where . " ORDER BY order_date ASC, sale ASC")->find_array();
                
                foreach ($tmpOrders as $key => $order) {
                    $tmpCart[$order['sale']] = self::for_table(PREFIX . 'shopping_cart')
                        ->where('order_id', $order['id'])
                        ->find_array();
                }
                
                $orders = [
                    "orders" => $tmpOrders,
                    "cart"   => $tmpCart
                ];
                break;
        }
            
        return $orders;
    }
    
    /**
     * Fetch order details
     * @author Bobi <me@borislazarov.com> on 11 Dec 2014
     * @return array
     */
    public function fetchOrder($id, $information = "normal") {
        
        if ($information != "normal") {
            $objOrder = new Order(['sale' => $id], ["id"]);
            $id = $objOrder->getId();
        }
        
        $order = self::for_table(PREFIX . "orders")
            ->table_alias('o')
            ->join(PREFIX . 'customers', array('o.id', '=', 'c.order_id'), 'c')
            ->where('id', $id)
            ->find_array();
            
        $shoppingCart = self::for_table(PREFIX . 'shopping_cart')
            ->where('order_id', $id)
            ->find_array();
        
        return [$order, $shoppingCart];
        
    }
    
    /**
     * Fetch other orders for current person in current time period
     * @author realdark <me@borislazarov.com> on 30 Dec 2014
     * @return string
     */
    public function fetchOtherOrders($name) {
        
        $name = trim($name);
        
        $orders = self::for_table(PREFIX . 'orders')
            ->table_alias('o')
            ->select("o.sale")
            ->select("o.order_date")
            ->join(PREFIX . 'customers', array('o.id', '=', 'c.order_id'), 'c')
            ->where("o.shipped_date", "0000-00-00 00:00:00")
            ->where_like("c.shipping_adress", "%$name%")
            ->find_array();
        
        return $orders;
    }
    
    /**
     * Display users with 1+ adress
     * @author realdark <me@borislazarov.com>
     * @return array
     */
    public static function displayAdressMore($type = "arranged") {
        $newOrders = self::for_table(PREFIX . "orders")
            ->table_alias('o')
            ->select('c.full_name')
            ->join(PREFIX . 'customers', array('o.id', '=', 'c.order_id'), 'c')
            ->where('o.shipped_date', '0000-00-00 00:00:00')
            ->find_array();
            
        $arrAdressMore = [];
            
        foreach ($newOrders as $key => $name) {
            $arrAdress = self::for_table(PREFIX . "orders")
                ->table_alias('o')
                ->select('o.sale')
                ->select('c.shipping_adress')
                ->select('c.full_name')
                ->join(PREFIX . 'customers', array('o.id', '=', 'c.order_id'), 'c')
                ->where('c.full_name', $name['full_name'])
                ->order_by_desc('c.id')
                ->find_array();
             
            if (count($arrAdress) > 1) {
                
                foreach($arrAdress as $value) {
                    
                    $tmpArr = [];
                    
                    foreach ($arrAdress as $tmpVar) {
                        
                        $tmpArr[] = $tmpVar['shipping_adress'];
                    }
                    
                    $arrKey = array_keys($tmpArr, $value['shipping_adress']);
                    
                    if (count($arrKey) > 0) {
                        $arrAdressMore[$value['full_name']] = $value['sale'];
                        break;
                    }
                    
                }
            }
        }
        
        if ($type == "arranged") {
            foreach($arrAdressMore as $key => $value) {
                $moreThanOneAdress[] = [
                    'name' => $key,
                    'sale' => $value
                ]; 
            }
        } else {
            $moreThanOneAdress = $arrAdressMore;
        }
        
        return $moreThanOneAdress;
    }

    /**
     * Fetch active orders
     */
    public function fetchActiveOrders() {
        $orders = self::for_table(PREFIX . 'orders')->raw_query("
            SELECT id, sale, order_date, shipped_date, scheduled_to_ship_date FROM " . PREFIX . "orders
            WHERE shipped_date = '0000-00-00 00:00:00' ORDER BY id ASC")->find_array();

        return $orders;
    }

    /**
     * Get last product form db shopping cart
     */
    private static function getLastProductFromOrders() {
        $product = self::for_table(PREFIX . 'shopping_cart')
            ->select('created_system')
            ->order_by_desc('id')
            ->limit(1)
            ->offset(0)
            ->find_one();

        return $product->created_system;
    }

    /*
     * Enable or disable btn
     */
    public static function enableBtn() {
        $lastProductDate = self::getLastProductFromOrders();

        //Current time
        $dtNow    = new \DateTime();

        $created = new \DateTime($lastProductDate);
        $dDiff = $created->diff($dtNow);

        $minutes = $dDiff->days * 24 * 60;
        $minutes += $dDiff->h * 60;
        $minutes += $dDiff->i;

        if ($minutes > 5) {
            return true;
        } else {
            return false;
        }
    }
}