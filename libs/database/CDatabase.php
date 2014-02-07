<?php

/**
 * CDatabase class files.
 *
 * @author Sergei Novickiy <edii87shadow@gmail.com>
 * @copyright Copyright &copy; 2013 
 */

class CDatabase extends CApplicationComponent {
    
    public static $db = array();

    // settings params
    var $_params = 'main';
    var $_key = NULL;
    
    var $_configs;
    var $_connection;
    
    private $_definitions = array();
    
    public $_databaseDefinition;
    public $_boxesDefinition;
    
    function __construct( $params, $_key = NULL ) {
        
        $this->setParams( $params );
        $this->setKey(  $_key );
        
        // set _configs
        $this->_getDb();
        
        //if(!empty($properties) and isset($properties))
          //      $this->setProperties( $properties );
        
        $this->_getDefinitions(); // load definitions from controller
        $this->database();
        // $this->_loadDbDefionition(); // load definitions
    }
    
    
    
    protected function _getDb() {
        $this->_configs = \init::app()->getDb();
        
        if(!$this->_configs or !is_array($this->_configs)) {
              
            throw new CDbException(\init::t('init','not create configs: {error}',
                           array('{error}' => get_class($this))));
        } else {
           if(!array_key_exists('main', $this->_configs)) {
                throw new CDbException(\init::t('init','Create configs settings db[main] => ["localhost" = > , and other]: {error}',
                           array('{error}' => get_class($this))));
            }   
        }
        return $this->_configs;
    }
    
    
    
    public function setProperties(array $properties) {
        foreach ($properties as $property => $value) {
        	$method = 'set'. ucfirst($property);
            $this->{$method}($value);
        }
        return $this;
    }
    
    
    
    // params
    protected function getParams() {
        return $this->_params; 
    }
    
    protected function setParams( $params ) {
        $this->_params = $params;
    }
    
    
    // return
    protected function getKey() {
        return $this->_key; 
    }
    
    protected function setKey( $key ) {
        $this->_key = $key;
    }
    
    public function getConnection() {
        $_p = $this->getParams();
        if(empty($_p)) $_p = 'main';
        return $this->_connection = self::$db[$_p];
         
    }
    
  
    
    /*
     * 
     * connect DB;
     * 
     */
    private function database() {
         
                try {                        
                        if(!is_array($this->_configs)) return null;
                        
                        Database::setSettings($this->_configs);
                        $_settings = Database::getSettings();
                        self::$db = Database::getConnection($this->getParams(), $this->getKey()); // $target = 'main', $key = NULL   
                        
                       
                } catch(Exception $e) {
                    
                    
                    if(DEBUG) {
                            throw new \CDbException('CDatabase failed to open the DB connection: '.
                                    $e->getMessage(),(int)$e->getCode(),$e->errorInfo);
                    } else {
                            \init::log($e->getMessage(), \CLogger::LEVEL_ERROR,'exception.CDbException');
                            throw new \CDbException('CDatabase failed to open the DB connection.', (int)$e->getCode(), $e->errorInfo);
                    }
                    
                    
                    
                    
                }

                
              
	}
        
        
}