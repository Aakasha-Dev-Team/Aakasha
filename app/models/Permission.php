<?php namespace models;

/**
 * Permission model
 * @author realdark 13 Jan 2015
 */
class Permission extends \core\model {
    
    function __construct($key = NULL, $select = []) {
        parent::_construct($key, $select);
    }
    
    public static function permission($sectionName = NULL) {
        //Fetch section id
        $objPermissionSection = new PermissionSection(["name" => $sectionName, "parent" => 0], ["id"]);
        $soctionId            = $objPermissionSection->getId();
        
        //Fetch user id
        $objUser = new User();
        $userId  = $objUser->fetchId();
        
        //return value
        $data = [];
        
        $query = self::for_table(PREFIX . "permission")
            ->table_alias("p")
            ->select("p.permission")
            ->select("p.view")
            ->select("p.edit")
            ->select("ps.name")
            ->join(PREFIX . 'permission_sections', array('p.section_id', '=', 'ps.id'), 'ps')
            ->where("p.user_id", $userId)
            ->where("p.main_section_id", $soctionId)
            ->find_array();
            
        foreach ($query as $key => $value) {
            $data[$value['name']] = [
                'permission' => $value['permission'],
                'view'       => $value['view'],
                'edit'       => $value['edit']
            ];
        }
            
        return $data;
    }
    
    /**
     * Fetch Permissions
     * @author realdark <me@borislazarov.com> on 21 Jan 2015
     */
    public function fetchPermissions($userId) {
        $query = self::for_table(PREFIX . "permission")
            ->table_alias("p")
            ->select("p.permission")
            ->select("p.view")
            ->select("p.edit")
            ->select("ps.name")
            ->select("ps1.name", "main_department")
            ->join(PREFIX . 'permission_sections', array('p.section_id', '=', 'ps.id'), 'ps')
            ->join(PREFIX . 'permission_sections', array('p.main_section_id', '=', 'ps1.id'), 'ps1')
            ->where("p.user_id", $userId)
            ->find_array();

        return $query;
    }
   
    /**
     * Update permission
     */
    public static function updatePermission($key, $value, $userId) {
        $arrKey = explode("_", $key);
        
        //Get parrent id
        $objPermissionSection = new PermissionSection(['name' => ucfirst($arrKey[0]), 'parent' => 0], ['id']);
        $paretntId            = $objPermissionSection->getId();
        
        //Get slave id
        unset($arrKey[0]);
        $string = implode("_", $arrKey);
        
        $objPermissionSection = new PermissionSection(['name' => $string, 'parent' => $paretntId], ['id']);
        $slaveId              = $objPermissionSection->getId();
        
        $obj = self::for_table(PREFIX . "permission")
            ->where('main_section_id', $paretntId)
            ->where('section_id', $slaveId)
            ->where('user_id', $userId)
            ->find_one();
        
        if ($obj != false) {
            switch ($value) {
                case "view":
                    $obj->setPermission(1);
                    $obj->setView(1);
                    $obj->setEdit(0);
                    break;
                
                case "no permission":
                    $obj->setPermission(0);
                    $obj->setView(0);
                    $obj->setEdit(0);
                    break;
                
                case "edit":
                    $obj->setPermission(1);
                    $obj->setView(1);
                    $obj->setEdit(1);
                    break;
            }
            
            try {
                $obj->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
        }
    }
    
    /**
     * Fetch Permissions
     * @author realdark <me@borislazarov.com> on 21 Jan 2015
     */
    public function fetchDepartmentPermissions($depId) {
        $query = self::for_table(PREFIX . "department_permissions")
            ->table_alias("p")
            ->select("p.permission")
            ->select("p.view")
            ->select("p.edit")
            ->select("ps.name")
            ->select("ps1.name", "main_department")
            ->join(PREFIX . 'permission_sections', array('p.section_id', '=', 'ps.id'), 'ps')
            ->join(PREFIX . 'permission_sections', array('p.main_section_id', '=', 'ps1.id'), 'ps1')
            ->where("p.department_id", $depId)
            ->find_array();

        return $query;
    }
    
    /**
     * Update permission
     */
    public static function updateFepratmentPermission($key, $value, $departmentId) {
        $arrKey = explode("_", $key);
        
        //Get parrent id
        $objPermissionSection = new PermissionSection(['name' => ucfirst($arrKey[0]), 'parent' => 0], ['id']);
        $paretntId            = $objPermissionSection->getId();
        
        //Get slave id
        unset($arrKey[0]);
        $string = implode("_", $arrKey);
        
        $objPermissionSection = new PermissionSection(['name' => $string, 'parent' => $paretntId], ['id']);
        $slaveId              = $objPermissionSection->getId();
        
        $obj = self::for_table(PREFIX . "department_permissions")
            ->where('main_section_id', $paretntId)
            ->where('section_id', $slaveId)
            ->where('department_id', $departmentId)
            ->find_one();
        
        if ($obj != false) {
            switch ($value) {
                case "view":
                    $obj->setPermission(1);
                    $obj->setView(1);
                    $obj->setEdit(0);
                    break;
                
                case "no permission":
                    $obj->setPermission(0);
                    $obj->setView(0);
                    $obj->setEdit(0);
                    break;
                
                case "edit":
                    $obj->setPermission(1);
                    $obj->setView(1);
                    $obj->setEdit(1);
                    break;
            }
            
            try {
                $obj->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
        }
    }
    
    /**
     * Update permission
     */
    public static function createDepartment($key, $value, $departmentId) {
        $arrKey = explode("_", $key);
        
        //Get parrent id
        $objPermissionSection = new PermissionSection(['name' => ucfirst($arrKey[0]), 'parent' => 0], ['id']);
        $paretntId            = $objPermissionSection->getId();
        
        //Get slave id
        unset($arrKey[0]);
        $string = implode("_", $arrKey);
        
        $objPermissionSection = new PermissionSection(['name' => $string, 'parent' => $paretntId], ['id']);
        $slaveId              = $objPermissionSection->getId();
        
        $obj = new DepartmentPermissions();
        $obj->setMainSectionId($paretntId);
        $obj->setSectionId($slaveId);
        $obj->setDepartmentId($departmentId);
        
        if ($obj != false) {
            switch ($value) {
                case "view":
                    $obj->setPermission(1);
                    $obj->setView(1);
                    $obj->setEdit(0);
                    break;
                
                case "no permission":
                    $obj->setPermission(0);
                    $obj->setView(0);
                    $obj->setEdit(0);
                    break;
                
                case "edit":
                    $obj->setPermission(1);
                    $obj->setView(1);
                    $obj->setEdit(1);
                    break;
            }
            
            try {
                $obj->save();
            } catch (\Exception $e) {
                \core\logger::exception_handler($e);
            }
        }
    }
}