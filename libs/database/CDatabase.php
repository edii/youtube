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
    
    protected function _getDefinitions() {
        $this->_definitions = \init::app()->getDefinition();
        if(!is_array($this->_definitions) or empty($this->_definitions) or !isset($this->_definitions)) {
            return null;
        }
        
        if(is_array($this->_definitions)) {
            if(isset($this->_definitions['databaseDefinition']) and is_array($this->_definitions['databaseDefinition']))
                $this->setDatabaseDefinition( $this->_definitions['databaseDefinition'] );
           // if(isset($this->_definitions['boxesDefinition']) and is_array($this->_definitions['boxesDefinition']))
             //   $this->setBoxesDefinition( $this->_definitions['boxesDefinition'] );
        }
        
        return $this->_definitions;
        
    }
    
      /**
     * 
     * load dbDefinition
     * return array fields from dbDefionitions
     * 
     */
    /*
    private function _loadDbDefionition() {
        $_result = array();
        $_dbDefinition = $this->getDatabaseDefinition();
        if(is_array($_dbDefinition) and count($_dbDefinition) > 0) {
            $_sql = '';
            $_db = $this->getConnection();
            $options['target'] = 'main';
            
            foreach($_dbDefinition['t'] as $key => $value) {
                $_sql = "SELECT ".$value." FROM ".trim($key);
                $_result[$key] = $_db -> query($_sql, array(), $options)-> fetchAll();
            }
            
            $this->setDatabaseDefinition( $_result );
        }
    }
    */
    
    public function setProperties(array $properties) {
        foreach ($properties as $property => $value) {
        	$method = 'set'. ucfirst($property);
            $this->{$method}($value);
        }
        return $this;
    }
    
    /**
     * register variable, settings database 
     * @return databaseDefinition ['t'][['tabel_name'] => '', ['key'] => '']
     */
    public function getDatabaseDefinition() {
        return $this-> _databaseDefinition;
    }
    protected function setDatabaseDefinition( array $_databaseDefinition ) {
        $this-> _databaseDefinition = $_databaseDefinition;
        return $this-> _databaseDefinition;
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
                    
                    
                   // \init::log('Error in preparing SQL: '.$this->getText(), \CLogger::LEVEL_ERROR,'system.db.CDbCommand');
                   // $errorInfo = $e instanceof PDOException ? $e->errorInfo : null;
                   // throw new CDbException(\init::t('yii','CDbCommand failed to prepare the SQL statement: {error}',
                   //         array('{error}' => $e->getMessage())),(int)$e->getCode(), $errorInfo);
                    
                    
                    
                }

                
              
	}
        
        
}