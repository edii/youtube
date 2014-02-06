<?php
namespace core;

/**
 * Class Base
 */
class Request
{

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
    
        
}      
