<?php
error_reporting( -1 );

defined('BEGIN_TIME') or define('BEGIN_TIME', microtime(true));

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));

defined('PATH') or define('PATH',dirname(__FILE__));
defined('PATH_LIBS') or define('PATH_LIBS',dirname(__FILE__).DIRECTORY_SEPARATOR."libs");
defined('DS') or define('DS', DIRECTORY_SEPARATOR );

require('/core/Base.php');

class init extends core\Base {
    //put your code here
}
// Base::autoload();
spl_autoload_register(['init', 'autoload'], true, true);
init::$classMap = include('/core/classes.php');

$application = new core\Application();
$application->run();


