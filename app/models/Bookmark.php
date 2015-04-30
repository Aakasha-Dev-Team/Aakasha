<?php namespace models;

/**
 * Bookmark model
 * @author
 */
class Bookmark extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Fetch bookmarks
     * @author realdark <me@borislazarov.com> on 23 Feb 2015
     * @return void
     */
    public static function fetchBookmarks() {
        $objUser = new User();
        $userId  = $objUser->fetchId();
        $query   = [];
        
        if (isset($userId)) {
            $query = self::for_table(PREFIX . "bookmarks")
                ->where('user_id', $userId)
                ->order_by_asc('id')
                ->find_array();
        }
        
        return $query;
    }
    
    public static function deleteBookmarks() {
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        if (isset($userId)) {
            $query = self::for_table(PREFIX . "bookmarks")
                ->where('user_id', $userId)
                ->delete_many();
        }
    }
    
}