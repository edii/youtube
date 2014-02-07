<?php
error_reporting( -1 );

defined('BEGIN_TIME') or define('BEGIN_TIME', microtime(true));

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

defined('PATH') or define('PATH',dirname(__FILE__));
defined('PATH_LIBS') or define('PATH_LIBS',dirname(__FILE__).DIRECTORY_SEPARATOR."libs");

require('/core/Base.php');

class init extends core\Base {
    //put your code here
}
// Base::autoload();
spl_autoload_register(['init', 'autoload'], true, true);
init::$classMap = include('/core/classes.php');

$application = new core\Application();

//
//$youtube = $application->getYoutube();
//
//$youtube -> run(array('key' =>'AIzaSyBgru7bk5o6_oZv9UP-Q9Jy_VHpjL2CE3o'));
//
//echo "<pre>";
//var_dump( $youtube );
//echo "</pre>";
//
//print_r($youtube->getVideoInfo('rie-hPVJ7Sw'));


$DEVELOPER_KEY = 'yMc6xPT8bBxU8lkhkHhuTeYM';
$client = \init::$app -> getComponent('Google_Client');
$client->setDeveloperKey($DEVELOPER_KEY);



$_services = \init::$app -> getComponent('Google_Service_YouTube', $client);




$searchResponse = $_services->search->listSearch('id,snippet', array(
  'q' => 'shadow87edii',
  'maxResults' => $_GET['maxResults'],
));


echo "<pre>";
var_dump($searchResponse);
echo "</pre>";
die('services');


echo "<hr />";




//$_test = spl_autoload_functions();
//$_test = spl_classes ();
//echo "<pre>";
//var_dump(init::$classMap);
//echo "</pre>";

// die('BLAAAAAAAAAAAAAAAAAaaaa');

