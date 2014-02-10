<?php

class HomeController extends core\Controller
{
        public static $_db;


        public $layout = 'index'; //'column1'

	private $_users;

        private $_auth = false;
        private $_validate = false;
        
        public function init() {
           // $this -> _users = \init::app() -> getModels('auth/users');
           self::$_db = \init::$app -> getDBConnector();
        }
        
        /**
         * load index admin
         */
	public function actionIndex() {
            self::$_db = \init::$app -> getDBConnector();
            
            $_query = self::$_db -> query("SELECT * FROM user") -> fetchAll();
            
//            $_query = self::$db -> query("SELECT userID as id, 
//                                                     login as login, 
//                                                     email as email
//                                              FROM user ") -> fetchAll();
            echo "<pre>";
            var_dump( $_query );
            echo "</pre>";
            
            $this->render('index', array(
                        'test' => array('Work')
                    )); 
            
        }
}
