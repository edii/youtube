<?php

class HomeController extends core\Controller
{
        public static $_db;

        public $layout = 'index'; 

        protected $_request;
        
        public function init() {
           self::$_db = \init::$app -> getDBConnector();
           $this -> _request = \init::$app -> getRequest();
        }
        
        /**
         * load index admin
         */
	public function actionIndex() {
           
           // $_query = self::$_db -> query("SELECT * FROM banners") -> fetchAll();
            
//            $_query = self::$db -> query("SELECT userID as id, 
//                                                     login as login, 
//                                                     email as email
//                                              FROM user ") -> fetchAll();
//            echo "<pre>";
//            var_dump( $_query );
//            echo "</pre>";
            
            $this->render('form'); 
            
        }
        
        public function actionYoutube() {
            // echo youtube
            $_result = array();
            $_limit = ($this -> _request -> getParam('_limit')) ? (int)$this -> _request -> getParam('_limit'): 50;
            $_url = $this -> _request -> getParam('url_address');
            $_channel_address = false;
            
            if($_parce_url  = parse_url( $_url ) 
                        and isset($_parce_url['scheme']) and !empty($_parce_url['scheme']) 
                        and isset($_parce_url['host']) and !empty($_parce_url['host'])
                        and isset($_parce_url['path']) and !empty($_parce_url['path'])
                    ) {
                
                
                
                if($_parce_url['scheme'] == 'http' and preg_match('/([www\.youtube\.com]|[youtube\.com])/i', $_parce_url['host'])) {
                    
                    $_path = explode('/', trim($_parce_url['path'],'/'));
                    $_get_url = false;
                    if(is_array($_path) and count($_path) > 0) {
                        $_get_url[$_path[0]] = (isset($_path[1]) and !empty($_path[1])) ? $_path[1]: false;
                    }
                    
                    if(!$_get_url 
                            or (isset($_get_url['user']) and empty($_get_url['user']))
                            or !isset($_get_url['user'])) 
                        $_result['error'] = ' Введите праельно youtube url! ';
                    
                    $_channel_address = $_get_url['user'];
                } 
                
                
            } else {
               $_channel_address = htmlspecialchars( stripcslashes( $_url ) ); 
            }
            
           
            $_youtube = \init::$app -> getYoutube() -> init( array('user' => $_channel_address, 'limit' => $_limit) );
            
            $data = $_youtube->apiCall();
            $videos = $_youtube->getVideosFromData($data);

            //$videos = $yt->getUserSubscriptions();

            //$videos = $yt->getStandardFeed(array('limit' => 4, 'feed' => 'most_viewed', 'time' => 'this_month'));

            //$yt->setOptions(array("time"=>"this_month", 'limit' => 4));
            //$videos = $yt->getStandardFeed("most_viewed");

            //$videos = $yt->searchForVideos( array('search' => 'seat race crash', 'limit' => 5) );
            //$videos = $yt->searchForVideos( "seat race crash" );

        //    $_video = $yt->getSingleVideo("6bmsspD5_bY");
        //    
            
            $_result['result'] = $videos;
            
            echo json_encode( $_result );
        }
}
