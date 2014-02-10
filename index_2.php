<?php
session_start();

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


$client_id = '825005290908.apps.googleusercontent.com';
$client_secret = 'yMc6xPT8bBxU8lkhkHhuTeYM';
$redirect_uri = 'http://youtube.dev:7777/test.php';


$client = \init::$app -> getComponent('Google_Client');


$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope("https://www.googleapis.com/auth/youtube");


if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $authUrl = $client->createAuthUrl();
}


echo "<pre>";
var_dump( $_SESSION );
echo "</pre>";
die('SESSION');


$_services = \init::$app -> getComponent('Google_Service_YouTube', $client);

$searchResponse = $_services->search->listSearch('id,snippet', array(
  'q' => 'qwerty',
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

