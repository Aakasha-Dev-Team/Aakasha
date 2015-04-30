<?php namespace models;

/**
 * PermissionSection model
 * @author realdark on 13 Jan 2015
 */
class PermissionSection extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Fetch sections
     * @author realdark <me@borislazarov.com> on 14 Jan 2014
     * @return array
     */
    public function fetchSections($parent = 0) {
        $query = self::for_table(PREFIX . "permission_sections")
            ->where("parent", $parent)
            ->find_array();
        
        foreach($query as $key => $value) {
                $data[$key]['name'] = $value['name'];
                $this->fetchSections($value['id']) == NULL ? NULL : $data[$key]['sections'] = $this->fetchSections($value['id']);
        }
        
        return $data;

    }
    
}