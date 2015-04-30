<?php namespace models;

/**
 * Department model
 * @author realdark <me@borislazarov.com> on 27 Jan 2015
 */
class Department extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    /**
     * Fetch all departments
     * @author realdark <me@borislazarov.com> on 27 Jan 2015
     * @return array
     */
    public function fetchDepartments() {
        
        $query = self::for_table(PREFIX . "departments")
            ->find_array();
            
        return $query;
        
    }
    
    /**
     * Fetch users by department
     * @author realdark <me@borislazarov.com> on 28 Jan 2015
     * @param integer $departmentId Depratment id
     * @return array
     */
    public static function fetchUsersByDepartment($departmentId) {
        $query = self::for_table(PREFIX . "users")
            ->select("name")
            ->where("department_id", $departmentId)
            ->find_array();
            
        return $query;
    }
    
}