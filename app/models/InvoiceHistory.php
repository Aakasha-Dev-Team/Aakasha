<?php namespace models;

/**
 * Invoice history model
 * @author
 */
class InvoiceHistory extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Fetch invoices by id
     * @author realdark <me@borislazarov.com> on 29 Dec 2014
     * @return array
     */
    public function fetchInvoices($orderId) {
        
        $query = self::for_table(PREFIX . "invoices_history")
            ->where("order_id", $orderId)
            ->find_array();
            
        return $query;
        
    }
    
}