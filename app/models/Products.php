<?php namespace models;

/**
 * Products model
 * @author
 */
class Products extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Fetch all items
     * @author realdark <me@borislazarov.com> on 26 Feb 2015
     * @return void
     */
    public static function fetchALl() {
        $query = self::for_table(PREFIX . "products")->find_array();
        
        return $query;
    }
    
}