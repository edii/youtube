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
        
        public function actionUploder() {
            $content = null;
            $path = array(
                1 => 'путь к файлу'
            );
            
            $_path = $path[ rand(1, count($path)) ];
            
            if(!empty($n_id) and file_exists($_path)) {
			//Delete Old Photo START
			$sql = "SELECT n_photo FROM `news` WHERE n_id=".$n_id;
                        $result = self::$db -> query($sql) -> fetchAll();
                        
                        if(is_array($result) and count($result) > 0) :
                            foreach($result as $_k => $_item):
                                $result[$_k] = (array)$_item;
                            endforeach;
                        else:
                            return null;
                        endif;
                        
			$n_photo = array_shift($result)['n_photo'];

			if(is_string($n_photo) && strlen($n_photo)){
				$n_photo_path = PATH.'/files/origin/'.$n_photo;
				unlink($n_photo_path);
			}
			//Delete Old Photo END

			$n_photo = sprintf('%07u_%u.jpg', $n_id, time());
			$n_photo_dir = PATH.'/files/origin/';
			if(!file_exists($n_photo_dir) || !is_dir($n_photo_dir)) mkdir($n_photo_dir, 0777);
			$n_photo_path = $n_photo_dir.$n_photo;

			$res = move_uploaded_file($_path, $n_photo_path);
			if($res){
                            $sql = "UPDATE `news` SET n_photo='$n_photo' WHERE n_id=".$n_id;
                            self::$db -> query($sql);
                            $sql_update = "SELECT n_photo FROM `news` WHERE n_id=".$n_id;
                            $result_update = self::$db ->query($sql_update)-> fetchAll();
                             if(is_array($result_update) and count($result_update) > 0) :
                                 foreach($result_update as $_k => $_item):
                                    $result[$_k] = (array)$_item;
                                 endforeach;
                             else:
                                return null;
                            endif;
                            
                            $content = $result_update;
			}
            }
            return $content;
        }
        
        public function cropIamge() {
            $_result = false;
            $_error = false;
            $_res = array();
            
            
            if($_REQUEST['status'] == 'crop') {
                    
                    $filesname = $_REQUEST['filesname'];
                    $news_id = ($_REQUEST['news_id']) ? $_REQUEST['news_id']: 'init';
                    
                    $target_path_75_54 =    PATH . '/files/news/cropr_75x54';
                    $target_path_115_54 =   PATH . '/files/news/cropr_115x54';
                    $target_path_180_135 =  PATH . '/files/news/cropr_180x135';
                    $target_path_300_140 =  PATH . '/files/news/cropr_300x140';
                    $target_path_610_300 =  PATH . '/files/news/cropr_610x300';
                    
                    $target_path_300_167 =  PATH . '/files/news/cropr_300x167';
                    $target_path_170_110 =  PATH . '/files/news/cropr_170x110';
                    $target_path_90_90 =    PATH . '/files/news/cropr_90x90';
                            
                    $crop_folder = PATH . '/files/news/croprtm';
                    
                    if ( ! is_dir($target_path_75_54)) {
				mkdir($target_path_75_54, 0777, TRUE);
			}  
                    
                    if ( ! is_dir($target_path_115_54)) {
				mkdir($target_path_115_54, 0777, TRUE);
			} 
                        
                    if ( ! is_dir($target_path_180_135)) {
				mkdir($target_path_180_135, 0777, TRUE);
			}    
                        
                   if ( ! is_dir($target_path_300_140)) {
				mkdir($target_path_300_140, 0777, TRUE);
			}  
                        
                   if ( ! is_dir($target_path_610_300)) {
				mkdir($target_path_610_300, 0777, TRUE);
			} 
                        
                   if ( ! is_dir($target_path_300_167)) {
				mkdir($target_path_300_167, 0777, TRUE);
			} 
                        
                   if ( ! is_dir($target_path_170_110)) {
				mkdir($target_path_170_110, 0777, TRUE);
			}     
                      
                   if ( ! is_dir($target_path_90_90)) {
				mkdir($target_path_90_90, 0777, TRUE);
			}
                        
                    if ( ! is_dir($crop_folder)) {
				mkdir($crop_folder, 0777, TRUE);
			}
                     $tmp_name = PATH .'/files/origin/'.$filesname;   
                     $_crop_file = $crop_folder.'/'.$filesname;   
                        
                    
                        /* 75x54 */
                        if(file_exists($target_path_75_54.'/'.$filesname)) {
                            @unlink($target_path_75_54.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        $img->resize(75, 54)->save($target_path_75_54.'/'.$filesname);
                        
                        /* 180x135 */
                        if(file_exists($target_path_180_135.'/'.$filesname)) {
                            @unlink($target_path_180_135.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        $img->resize(180, 135)->save($target_path_180_135.'/'.$filesname);
                        
                        /* 115x54 */
                        if(file_exists($target_path_115_54.'/'.$filesname)) {
                            @unlink($target_path_115_54.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        $img->resize(115, 54)->save($target_path_115_54.'/'.$filesname);
                        
                        /* 300x140 */
                        if(file_exists($target_path_300_140.'/'.$filesname)) {
                            @unlink($target_path_300_140.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        $img->resize(300, 140)->save($target_path_300_140.'/'.$filesname);
                        
                        /* 300x167 */
                        if(file_exists($target_path_300_167.'/'.$filesname)) {
                            @unlink($target_path_300_167.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        $img->resize(300, 167)->save($target_path_300_167.'/'.$filesname);
                        
                        /* 170x110 */
                        if(file_exists($target_path_170_110.'/'.$filesname)) {
                            @unlink($target_path_170_110.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        $img->resize(170, 110)->save($target_path_170_110.'/'.$filesname);
                        
                        
                        /* 90x90 */
                        if(file_exists($target_path_90_90.'/'.$filesname)) {
                            @unlink($target_path_90_90.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        $img->resize(90, 90)->save($target_path_90_90.'/'.$filesname);
                        
                        
                        
                        /* 610x300 */
                        if(file_exists($target_path_610_300.'/'.$filesname)) {
                            @unlink($target_path_610_300.'/'.$filesname);
                        }     
                        $img = ResizeImages::createImage( $_crop_file );
                        // $img->resize(610, 300)->save($target_path_610_300.'/'.$filesname);
                        $img->cropCenter('610px', '300px')->save($target_path_610_300.'/'.$filesname);
                        
                        $_res = array('result' => true, 'error' => $_error, 'filename' => $filesname, 'stime' => time());
                        return $res;
            }        
            
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
                        $_result['error'] = ' Введите правельно youtube url! ';
                    
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
            
            if(is_array($videos) and count($videos) > 0) :
                foreach($videos as $key => $_item):
                    $videos[$key]['embed'] = $_youtube->getEmbedHTML( $_item );
                endforeach;
            endif;
            
            $_result['result'] = $videos;
            echo json_encode( $_result );
        }
}
