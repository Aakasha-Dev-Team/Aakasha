<?php namespace core;
use libraries\orm as ORM,
    helpers\globals as Globals;

/*
 * Model - the base model
 * Active Record website - http://idiorm.readthedocs.org
 *
 * @author Bobi <me@borislazarov.com> on 1 Nov 2014
 * @version 1.0
 */
abstract class Model extends ORM {
    
    //Active records vars
    private $_find;
    private $_create;
    private $_configure;
    
    //logger
    protected $logger;
    
    protected function _construct($key = NULL, $select = []) {
        
        //connect to Active Record
        self::configure([
                'connection_string' => DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME,
                'driver_options'    => [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'],
                'username'          => DB_USER,
                'password'          => DB_PASS
        ]);
        
        //fetch model name
        $modelName = explode("\\", get_class($this));
        $modelName = strtolower($modelName[1]);
        
        //fetch meldels config file
        $file = file_get_contents(APP_PATH . 'etc/models.xml');
        
        //convert xml file to object
        $xml = new \SimpleXMLElement($file);
        
        //set vars
        $this->_configure = new \stdClass();
        $this->_configure->active_record = $xml->models->$modelName->active_record;
        $this->_configure->db_table      = PREFIX . $xml->models->$modelName->db_table;
        $this->_configure->objectKey     = $key;
        $this->_configure->objectSelect  = $select;
        
        //active Active Records
        $this->_init();
        
    }
    
    /**
     * Init Active Record
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return void
     */
    private function _init() {
        $status  = $this->_configure->active_record;
        $dbTable = $this->_configure->db_table;
        
        //Fetch record information
        if (($status == "true") && (isset($this->_configure->objectKey))) {
            $this->_find = self::for_table($dbTable);
            
            if (count($this->_configure->objectSelect) > 0) {
                foreach ($this->_configure->objectSelect as $name) {
                    $this->_find->select($name);
                }
            }
            
            if (filter_var($this->_configure->objectKey, FILTER_VALIDATE_INT)) {
                $this->_find = $this->_find->find_one($this->_configure->objectKey);
                
            }
            
            if(is_array($this->_configure->objectKey)) {
                foreach ($this->_configure->objectKey as $key => $name) {
                    
                    $this->_find->where($key, $name);
                }
                $this->_find = $this->_find->find_one();
            }
            
        }
        
        //Create empty record
        if ($status == "true") {
            $this->_create = self::for_table($dbTable)->create();
        }
        
    }
    
    /**
     * Getter
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return mixed
     */
    public function __get($name) {
        return isset($this->_find->$name) ? $this->_find->$name : NULL;
    }
    
    /**
     * Setter
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return void
     */
    public function __set($name, $value) {
        if (isset($this->_configure->objectKey)) {
            $this->_find->$name = $value;
        }
        if (isset($this->_create)) {
            $this->_create->$name = $value;
        }
    }
    
    /**
     * Getter and Setter
     * @author Bobi <me@borislazarov.com> 25 Nov 2014
     * @return mixed
     */
    public function __call($name, $value) {
        $method = substr($name, 0, 3);
        $name   = substr($name, 3);
        $name   = strtolower(\helpers\util::decamelize($name));
        
        switch ($method) {
            case "get":
                return isset($this->_find->$name) ? $this->_find->$name : NULL;
                break;
                
            case "set":
                if (isset($this->_configure->objectKey)) {
                    $this->_find->$name = $value[0];
                }
                
                if (isset($this->_create)) {
                    $this->_create->$name = $value[0];
                }
                break;
        }
    }
    
     /**
     * Getter
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return mixed
     */   
    public function get($name) {
        return isset($this->_find->$name) ? $this->_find->$name : NULL;
    }
    
    /**
     * Setter
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return void
     */
    public function set($name, $value = null) {
        if (isset($this->_configure->objectKey)) {
            $this->_find->$name = $value;
        }
        if (isset($this->_create)) {
            $this->_create->$name = $value;
        }
    }
    
    /**
     * Set expression
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return void
     */
    public function set_expr($name, $value = NULL) {
        if (isset($this->_configure->objectKey)) {
            $this->_find->set_expr($name, $value);
        }
        if (isset($this->_create)) {
            $this->_create->set_expr($name, $value);
        }
    }
    
    /**
     * Count records
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return integer
     *
     */
    public function counting($where) {
        $status  = $this->_configure->active_record;
        $dbTable = $this->_configure->db_table;
        
        if ($status == "true") {
            $count = self::for_table($dbTable);
            
            if (isset($where)) {
                foreach ($where as $key => $name) {
                    $count->where($key, $name);
                }
            }
            
            return $count->count();
        }
    }
    
    /**
     * Save record
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return mixed
     */
    public function save() {
        $status  = $this->_configure->active_record;
        $dbTable = $this->_configure->db_table;
        
        if ($status == "true") {
            if (isset($this->_configure->objectKey)) {
                $this->_find->save();
                return true;
            } else {
                $this->_create->save();
                $id = $this->_create->id();
                $this->_create = self::for_table($dbTable)->create();
                return $id;
            }
        }
    }
    
    /**
     * Delete record
     * @author Bobi <me@borislazarov.com> 20 Nov 2014
     * @return void
     */
    public function delete() {
        $status  = $this->_configure->active_record;
        
        if (($status == "true") && (isset($this->_configure->objectKey))) {
            $this->_find->delete();
        }
    } 

}