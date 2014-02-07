<?php
namespace core;

use init;

class Application extends Module
{
	/**
	 * @event Event an event raised before the application starts to handle a request.
	 */
	const EVENT_BEFORE_REQUEST = 'beforeRequest';
	/**
	 * @event Event an event raised after the application successfully handles a request (before the response is sent out).
	 */
	const EVENT_AFTER_REQUEST = 'afterRequest';
	/**
	 * @event ActionEvent an event raised before executing a controller action.
	 * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
	 */
	const EVENT_BEFORE_ACTION = 'beforeAction';
	/**
	 * @event ActionEvent an event raised after executing a controller action.
	 */
	const EVENT_AFTER_ACTION = 'afterAction';
	/**
	 * @var string the application name.
	 */
	public $name = 'My Application';
	/**
	 * @var string the version of this application.
	 */
	public $version = '1.0';
	/**
	 * @var string the charset currently used for the application.
	 */
	public $charset = 'UTF-8';
	/**
	 * @var string the language that is meant to be used for end users.
	 * @see sourceLanguage
	 */
	public $language = 'en';
	/**
	 * @var string the language that the application is written in. This mainly refers to
	 * the language that the messages and view files are written in.
	 * @see language
	 */
	public $sourceLanguage = 'en';
	/**
	 * @var Controller the currently active controller instance
	 */
	public $controller;
	/**
	 * @var string|boolean the layout that should be applied for views in this application. Defaults to 'main'.
	 * If this is false, layout will be disabled.
	 */
	public $layout = 'main';
	/**
	 * @var integer the size of the reserved memory. A portion of memory is pre-allocated so that
	 * when an out-of-memory issue occurs, the error handler is able to handle the error with
	 * the help of this reserved memory. If you set this value to be 0, no memory will be reserved.
	 * Defaults to 256KB.
	 */
	public $memoryReserveSize = 262144;
	/**
	 * @var string the requested route
	 */
	public $requestedRoute;
	/**
	 * @var Action the requested Action. If null, it means the request cannot be resolved into an action.
	 */
	public $requestedAction;
	/**
	 * @var array the parameters supplied to the requested action.
	 */
	public $requestedParams;
	/**
	 * @var array list of installed Yii extensions. Each array element represents a single extension
	 * with the following structure:
	 *
	 * ~~~
	 * [
	 *     'name' => 'extension name',
	 *     'version' => 'version number',
	 *     'bootstrap' => 'BootstrapClassName',
	 * ]
	 * ~~~
	 */
	public $extensions = [];
	/**
	 * @var \Exception the exception that is being handled currently. When this is not null,
	 * it means the application is handling some exception and extra care should be taken.
	 */
	public $exception;

	/**
	 * @var string Used to reserve memory for fatal error handler.
	 */
	private $_memoryReserve;

        
	/**
	 * Constructor.
	 * @param array $config name-value pairs that will be used to initialize the object properties.
	 * Note that the configuration must contain both [[id]] and [[basePath]].
	 * @throws InvalidConfigException if either [[id]] or [[basePath]] configuration is missing.
	 */
	public function __construct($config = [])
	{
                $this->registerCoreComponents();
            
		init::$app = $this;
                $this->setTimeZone('UTC');
                
                
                
		//Component::__construct($config);
	}

	
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->initExtensions($this->extensions);
		parent::init();
	}

	/**
	 * Initializes the extensions.
	 * @param array $extensions the extensions to be initialized. Please refer to [[extensions]]
	 * for the structure of the extension array.
	 */
	protected function initExtensions($extensions)
	{
		foreach ($extensions as $extension) {
			if (!empty($extension['alias'])) {
				foreach ($extension['alias'] as $name => $path) {
					Yii::setAlias($name, $path);
				}
			}
			if (isset($extension['bootstrap'])) {
				/** @var Extension $class */
				$class = $extension['bootstrap'];
				$class::init();
			}
		}
	}

	/**
	 * Loads components that are declared in [[preload]].
	 * @throws InvalidConfigException if a component or module to be preloaded is unknown
	 */
	public function preloadComponents()
	{
		$this->getComponent('log');
		parent::preloadComponents();
	}

	/**
	 * Registers error handlers.
	 */
	public function registerErrorHandlers()
	{
		if (YII_ENABLE_ERROR_HANDLER) {
			ini_set('display_errors', 0);
			set_exception_handler([$this, 'handleException']);
			set_error_handler([$this, 'handleError'], error_reporting());
			if ($this->memoryReserveSize > 0) {
				$this->_memoryReserve = str_repeat('x', $this->memoryReserveSize);
			}
			register_shutdown_function([$this, 'handleFatalError']);
		}
	}

	/**
	 * Returns an ID that uniquely identifies this module among all modules within the current application.
	 * Since this is an application instance, it will always return an empty string.
	 * @return string the unique ID of the module.
	 */
	public function getUniqueId()
	{
		return '';
	}

	/**
	 * Sets the root directory of the application and the @app alias.
	 * This method can only be invoked at the beginning of the constructor.
	 * @param string $path the root directory of the application.
	 * @property string the root directory of the application.
	 * @throws InvalidParamException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		parent::setBasePath($path);
		Yii::setAlias('@app', $this->getBasePath());
	}

	/**
	 * Runs the application.
	 * This is the main entrance of an application.
	 * @return integer the exit status (0 means normal, non-zero values mean abnormal)
	 */
	public function run()
	{
		$this->trigger(self::EVENT_BEFORE_REQUEST);
		$response = $this->handleRequest($this->getRequest());
		$this->trigger(self::EVENT_AFTER_REQUEST);
		$response->send();
		return $response->exitStatus;
	}

	/**
	 * Handles the specified request.
	 *
	 * This method should return an instance of [[Response]] or its child class
	 * which represents the handling result of the request.
	 *
	 * @param Request $request the request to be handled
	 * @return Response the resulting response
	 */
	public function handleRequest($request)
	{
		if (empty($this->catchAll)) {
			list ($route, $params) = array('home/index',array('test' => 'BLAAAAAAAAAAAAAAa'));
		} else {
			$route = $this->catchAll[0];
			$params = array_splice($this->catchAll, 1);
		}
		try {
			
			$this->requestedRoute = $route;
			$result = $this->runAction($route, $params);
			if ($result instanceof Response) {
				return $result;
			} else {
				$response = $this->getResponse();
				if ($result !== null) {
					$response->data = $result;
				}
				return $response;
			}
		} catch (Exception $e) {
			throw new Exception($e->getMessage(), $e->getCode(), $e);
		}
	}

	private $_runtimePath;

	/**
	 * Returns the directory that stores runtime files.
	 * @return string the directory that stores runtime files.
	 * Defaults to the "runtime" subdirectory under [[basePath]].
	 */
	public function getRuntimePath()
	{
		if ($this->_runtimePath === null) {
			$this->setRuntimePath($this->getBasePath() . DIRECTORY_SEPARATOR . 'runtime');
		}
		return $this->_runtimePath;
	}

	/**
	 * Sets the directory that stores runtime files.
	 * @param string $path the directory that stores runtime files.
	 */
	public function setRuntimePath($path)
	{
		$this->_runtimePath = Yii::getAlias($path);
		Yii::setAlias('@runtime', $this->_runtimePath);
	}

	private $_vendorPath;

	/**
	 * Returns the directory that stores vendor files.
	 * @return string the directory that stores vendor files.
	 * Defaults to "vendor" directory under [[basePath]].
	 */
	public function getVendorPath()
	{
		if ($this->_vendorPath === null) {
			$this->setVendorPath($this->getBasePath() . DIRECTORY_SEPARATOR . 'vendor');
		}
		return $this->_vendorPath;
	}

	/**
	 * Sets the directory that stores vendor files.
	 * @param string $path the directory that stores vendor files.
	 */
	public function setVendorPath($path)
	{
		$this->_vendorPath = Yii::getAlias($path);
		Yii::setAlias('@vendor', $this->_vendorPath);
	}

	/**
	 * Returns the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_get().
	 * If time zone is not configured in php.ini or application config,
	 * it will be set to UTC by default.
	 * @return string the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-get.php
	 */
	public function getTimeZone()
	{
		return date_default_timezone_get();
	}

	/**
	 * Sets the time zone used by this application.
	 * This is a simple wrapper of PHP function date_default_timezone_set().
	 * Refer to the [php manual](http://www.php.net/manual/en/timezones.php) for available timezones.
	 * @param string $value the time zone used by this application.
	 * @see http://php.net/manual/en/function.date-default-timezone-set.php
	 */
	public function setTimeZone($value)
	{
		date_default_timezone_set($value);
	}

	/**
	 * Returns the database connection component.
	 * @return \yii\db\Connection the database connection
	 */
	public function getDb()
	{
		return $this->getComponent('db');
	}

	

	/**
	 * Returns the request component.
	 * @return \yii\web\Request|\yii\console\Request the request component
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * Returns the view object.
	 * @return View|\yii\web\View the view object that is used to render various view files.
	 */
	public function getView()
	{
		return $this->getComponent('view');
	}

        
        public function getYoutube() {
            return $this->getComponent( 'youtube' );
        }
	
	
	/**
	 * Handles uncaught PHP exceptions.
	 *
	 * This method is implemented as a PHP exception handler.
	 *
	 * @param \Exception $exception the exception that is not caught
	 */
	public function handleException($exception)
	{
		$this->exception = $exception;

		// disable error capturing to avoid recursive errors while handling exceptions
		restore_error_handler();
		restore_exception_handler();
		try {
			$this->logException($exception);
			if (($handler = $this->getErrorHandler()) !== null) {
				$handler->handle($exception);
			} else {
				echo $this->renderException($exception);
				if (PHP_SAPI === 'cli' && !YII_ENV_TEST) {
					exit(1);
				}
			}
		} catch (\Exception $e) {
			// exception could be thrown in ErrorHandler::handle()
			$msg = (string)$e;
			$msg .= "\nPrevious exception:\n";
			$msg .= (string)$exception;
			if (YII_DEBUG) {
				if (PHP_SAPI === 'cli') {
					echo $msg . "\n";
				} else {
					echo '<pre>' . htmlspecialchars($msg, ENT_QUOTES, $this->charset) . '</pre>';
				}
			}
			$msg .= "\n\$_SERVER = " . var_export($_SERVER, true);
			error_log($msg);
			exit(1);
		}
	}

	/**
	 * Handles PHP execution errors such as warnings, notices.
	 *
	 * This method is used as a PHP error handler. It will simply raise an `ErrorException`.
	 *
	 * @param integer $code the level of the error raised
	 * @param string $message the error message
	 * @param string $file the filename that the error was raised in
	 * @param integer $line the line number the error was raised at
	 *
	 * @throws ErrorException
	 */
	public function handleError($code, $message, $file, $line)
	{
		if (error_reporting() !== 0) {
			// load ErrorException manually here because autoloading them will not work
			// when error occurs while autoloading a class
			if (!class_exists('\\yii\\base\\Exception', false)) {
				require_once(__DIR__ . '/Exception.php');
			}
			if (!class_exists('\\yii\\base\\ErrorException', false)) {
				require_once(__DIR__ . '/ErrorException.php');
			}
			$exception = new ErrorException($message, $code, $code, $file, $line);

			// in case error appeared in __toString method we can't throw any exception
			$trace = debug_backtrace(false);
			array_shift($trace);
			foreach ($trace as $frame) {
				if ($frame['function'] == '__toString') {
					$this->handleException($exception);
					exit(1);
				}
			}

			throw $exception;
		}
	}

	/**
	 * Handles fatal PHP errors
	 */
	public function handleFatalError()
	{
		unset($this->_memoryReserve);

		// load ErrorException manually here because autoloading them will not work
		// when error occurs while autoloading a class
		if (!class_exists('\\yii\\base\\Exception', false)) {
			require_once(__DIR__ . '/Exception.php');
		}
		if (!class_exists('\\yii\\base\\ErrorException', false)) {
			require_once(__DIR__ . '/ErrorException.php');
		}

		$error = error_get_last();

		if (ErrorException::isFatalError($error)) {
			$exception = new ErrorException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
			$this->exception = $exception;
			// use error_log because it's too late to use Yii log
			error_log($exception);

			if (($handler = $this->getErrorHandler()) !== null) {
				$handler->handle($exception);
			} else {
				echo $this->renderException($exception);
			}

			exit(1);
		}
	}

	/**
	 * Renders an exception without using rich format.
	 * @param \Exception $exception the exception to be rendered.
	 * @return string the rendering result
	 */
	public function renderException($exception)
	{
		if ($exception instanceof Exception && ($exception instanceof UserException || !YII_DEBUG)) {
			$message = $exception->getName() . ': ' . $exception->getMessage();
			if (Yii::$app->controller instanceof \yii\console\Controller) {
				$message = Yii::$app->controller->ansiFormat($message, Console::FG_RED);
			}
		} else {
			$message = YII_DEBUG ? (string)$exception : 'Error: ' . $exception->getMessage();
		}
		if (PHP_SAPI === 'cli') {
			return $message . "\n";
		} else {
			return '<pre>' . htmlspecialchars($message, ENT_QUOTES, $this->charset) . '</pre>';
		}
	}

	/**
	 * Logs the given exception
	 * @param \Exception $exception the exception to be logged
	 */
	protected function logException($exception)
	{
		$category = get_class($exception);
		if ($exception instanceof HttpException) {
			$category = 'yii\\web\\HttpException:' . $exception->statusCode;
		} elseif ($exception instanceof \ErrorException) {
			$category .= ':' . $exception->getSeverity();
		}
		Yii::error((string)$exception, $category);
	}
        
        
        public function registerCoreComponents()
	{
                $_components = array(
			'youtube' =>  PATH_LIBS.'/api/Youtube.php',
                    
                        'Google_Client' => PATH_LIBS.'/api/Google/Client.php',
                        'Google_Service_YouTube' => PATH_LIBS.'/api/Google/Service/YouTube.php',
                        
                    
			'view' => 'yii\base\View',
		);
            
                foreach($_components as $_id => $_components) {
                    $this->setComponent($_id, $_components);
                }
                
		
	}
}
