<?php
namespace core;

/**
 * Class Base
 */
class Request
{

    public $_requestUri;
    
    /**
         * retun (array) $_REQUEST
         */
        public function getParams() {
            return $_REQUEST;
        }
    
        public function stripSlashes(&$data)
	{
		return is_array($data)?array_map(array($this,'stripSlashes'),$data):stripslashes($data);
	}
        
        public function getParam($name,$defaultValue=null)
	{
		return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $defaultValue);
	}

        
        public function getQuery($name,$defaultValue=null)
	{
		return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
	}
        
        public function getPost($name,$defaultValue=null)
	{
		return isset($_POST[$name]) ? $_POST[$name] : $defaultValue;
	}
        
        public function _getRoute( $route ) {
            if (strpos($route, '/') !== false) {
                list ($id, $route) = explode('/', $route, 2);
            } else {
                    $id = $route;
                    $route = '';
            }
            
            $_route = $id.'/'.$route;
            
            if($_arr = explode('/', trim($_route, '/')) and count($_arr) > 0) {
                $action = (isset($_arr[1]) and !empty($_arr[1])) ? $_arr[1]: \init::$app ->defaultRouteAction;
                $_controller = (isset($_arr[0]) and !empty($_arr[0])) ? $_arr[0] : \init::$app -> defaultRouteController;
                $_route = $_controller.'/'.$action;
            } else {
               $_route = \init::$app -> defaultRouteController; 
            }
           
            return $_route;   
        }
        
        public function getRequestUri()
	{
		if($this->_requestUri===null)
		{
			if(isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
				$this->_requestUri=$_SERVER['HTTP_X_REWRITE_URL'];
			elseif(isset($_SERVER['REQUEST_URI']))
			{
				$this->_requestUri=$_SERVER['REQUEST_URI'];
				if(!empty($_SERVER['HTTP_HOST']))
				{
					if(strpos($this->_requestUri,$_SERVER['HTTP_HOST'])!==false)
						$this->_requestUri=preg_replace('/^\w+:\/\/[^\/]+/','',$this->_requestUri);
				}
				else
					$this->_requestUri=preg_replace('/^(http|https):\/\/[^\/]+/i','',$this->_requestUri);
			}
			elseif(isset($_SERVER['ORIG_PATH_INFO']))  // IIS 5.0 CGI
			{
				$this->_requestUri=$_SERVER['ORIG_PATH_INFO'];
				if(!empty($_SERVER['QUERY_STRING']))
					$this->_requestUri.='?'.$_SERVER['QUERY_STRING'];
			}
			else
				throw new \CException(\init::t('init','CHttpRequest is unable to determine the request URI.'));
		}

		return $this->_requestUri;
	}
        
        public function getQueryString()
	{
		return isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'';
	}
    
        public function parsePathInfo($pathInfo)
	{
                // var_dump( $pathInfo );
            
		if($pathInfo==='')
			return;
		$segs=explode('/',$pathInfo.'/');
		$n=count($segs);
		for($i=0;$i<$n-1;$i+=2)
		{
			$key=$segs[$i];
			if($key==='') continue;
			$value=$segs[$i+1];
			if(($pos=strpos($key,'['))!==false && ($m=preg_match_all('/\[(.*?)\]/',$key,$matches))>0)
			{
				$name=substr($key,0,$pos);
				for($j=$m-1;$j>=0;--$j)
				{
					if($matches[1][$j]==='')
						$value=array($value);
					else
						$value=array($matches[1][$j]=>$value);
				}
				if(isset($_GET[$name]) && is_array($_GET[$name]))
					$value= array_merge( $_GET[$name], $value);
				$_REQUEST[$name]=$_GET[$name]=$value;
			}
			else
				$_REQUEST[$key]=$_GET[$key]=$value;
		}
	}
        
}      
