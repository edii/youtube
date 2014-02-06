<?php
error_reporting( -1 );

defined('BEGIN_TIME') or define('BEGIN_TIME', microtime(true));

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

require('/core/Base.php');

class init extends core\Base {
    //put your code here
}
// Base::autoload();
spl_autoload_register(['init', 'autoload'], true, true);
init::$classMap = include('/core/classes.php');

$application = new core\Application();
$application->run();

//$_test = spl_autoload_functions();
//$_test = spl_classes ();
echo "<pre>";
var_dump(init::$classMap);
echo "</pre>";

// die('BLAAAAAAAAAAAAAAAAAaaaa');

