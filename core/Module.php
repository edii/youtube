<?php

namespace core;

use init;

class Module extends Component
{
	/**
	 * @var array custom module parameters (name => value).
	 */
	public $params = [];
	/**
	 * @var array the IDs of the components or modules that should be preloaded right after initialization.
	 */
	public $preload = [];
	/**
	 * @var string an ID that uniquely identifies this module among other modules which have the same [[module|parent]].
	 */
	public $id;
	/**
	 * @var Module the parent module of this module. Null if this module does not have a parent.
	 */
	public $module;
	/**
	 * @var string|boolean the layout that should be applied for views within this module. This refers to a view name
	 * relative to [[layoutPath]]. If this is not set, it means the layout value of the [[module|parent module]]
	 * will be taken. If this is false, layout will be disabled within this module.
	 */
	public $layout;
	/**
	 * @var array mapping from controller ID to controller configurations.
	 * Each name-value pair specifies the configuration of a single controller.
	 * A controller configuration can be either a string or an array.
	 * If the former, the string should be the fully qualified class name of the controller.
	 * If the latter, the array must contain a 'class' element which specifies
	 * the controller's fully qualified class name, and the rest of the name-value pairs
	 * in the array are used to initialize the corresponding controller properties. For example,
	 *
	 * ~~~
	 * [
	 *   'account' => 'app\controllers\UserController',
	 *   'article' => [
	 *      'class' => 'app\controllers\PostController',
	 *      'pageTitle' => 'something new',
	 *   ],
	 * ]
	 * ~~~
	 */
	public $controllerMap = [];
	/**
	 * @var string the namespace that controller classes are in. If not set,
	 * it will use the "controllers" sub-namespace under the namespace of this module.
	 * For example, if the namespace of this module is "foo\bar", then the default
	 * controller namespace would be "foo\bar\controllers".
	 */
	public $controllerNamespace;
	/**
	 * @return string the default route of this module. Defaults to 'default'.
	 * The route may consist of child module ID, controller ID, and/or action ID.
	 * For example, `help`, `post/create`, `admin/post/create`.
	 * If action ID is not given, it will take the default value as specified in
	 * [[Controller::defaultAction]].
	 */
	public $defaultRoute = 'default';
	/**
	 * @var string the root directory of the module.
	 */
	private $_basePath;
	/**
	 * @var string the root directory that contains view files for this module
	 */
	private $_viewPath;
	/**
	 * @var string the root directory that contains layout view files for this module.
	 */
	private $_layoutPath;
	/**
	 * @var string the directory containing controller classes in the module.
	 */
	private $_controllerPath;
	/**
	 * @var array child modules of this module
	 */
	private $_modules = [];
	/**
	 * @var array components registered under this module
	 */
	private $_components = [];

	/**
	 * Constructor.
	 * @param string $id the ID of this module
	 * @param Module $parent the parent module (if any)
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($id, $parent = null, $config = [])
	{
		$this->id = $id;
		$this->module = $parent;
		parent::__construct($config);
	}

	/**
	 * Getter magic method.
	 * This method is overridden to support accessing components
	 * like reading module properties.
	 * @param string $name component or property name
	 * @return mixed the named property value
	 */
	public function __get($name)
	{
		if ($this->hasComponent($name)) {
			return $this->getComponent($name);
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named component is loaded.
	 * @param string $name the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset($name)
	{
		if ($this->hasComponent($name)) {
			return $this->getComponent($name) !== null;
		} else {
			return parent::__isset($name);
		}
	}

	/**
	 * Initializes the module.
	 * This method is called after the module is created and initialized with property values
	 * given in configuration. The default implementation will call [[preloadComponents()]] to
	 * load components that are declared in [[preload]].
	 *
	 * If you override this method, please make sure you call the parent implementation.
	 */
	public function init()
	{
	}

	/**
	 * Returns an ID that uniquely identifies this module among all modules within the current application.
	 * Note that if the module is an application, an empty string will be returned.
	 * @return string the unique ID of the module.
	 */
	public function getUniqueId()
	{
		return $this->module ? ltrim($this->module->getUniqueId() . '/' . $this->id, '/') : $this->id;
	}

	/**
	 * Returns the root directory of the module.
	 * It defaults to the directory containing the module class file.
	 * @return string the root directory of the module.
	 */
	public function getBasePath()
	{
		if ($this->_basePath === null) {
			$class = new \ReflectionClass($this);
			$this->_basePath = dirname($class->getFileName());
		}
		return $this->_basePath;
	}

	/**
	 * Sets the root directory of the module.
	 * This method can only be invoked at the beginning of the constructor.
	 * @param string $path the root directory of the module. This can be either a directory name or a path alias.
	 * @throws InvalidParamException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		// $path = Yii::getAlias($path);
		$p = realpath($path);
		if ($p !== false && is_dir($p)) {
			$this->_basePath = $p;
		} else {
			throw new InvalidParamException("The directory does not exist: $path");
		}
	}

	/**
	 * Returns the directory that contains the controller classes.
	 * Defaults to "[[basePath]]/controllers".
	 * @return string the directory that contains the controller classes.
	 */
	public function getControllerPath()
	{
		if ($this->_controllerPath !== null) {
			return $this->_controllerPath;
		} else {
			return $this->_controllerPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'controllers';
		}
	}

	/**
	 * Sets the directory that contains the controller classes.
	 * @param string $path the directory that contains the controller classes.
	 * This can be either a directory name or a path alias.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setControllerPath($path)
	{
		$this->_controllerPath = Yii::getAlias($path);
	}

	/**
	 * Returns the directory that contains the view files for this module.
	 * @return string the root directory of view files. Defaults to "[[basePath]]/view".
	 */
	public function getViewPath()
	{
		if ($this->_viewPath !== null) {
			return $this->_viewPath;
		} else {
			return $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
		}
	}

	/**
	 * Sets the directory that contains the view files.
	 * @param string $path the root directory of view files.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setViewPath($path)
	{
		$this->_viewPath = Yii::getAlias($path);
	}

	/**
	 * Returns the directory that contains layout view files for this module.
	 * @return string the root directory of layout files. Defaults to "[[viewPath]]/layouts".
	 */
	public function getLayoutPath()
	{
		if ($this->_layoutPath !== null) {
			return $this->_layoutPath;
		} else {
			return $this->_layoutPath = PATH . DIRECTORY_SEPARATOR . 'layouts';
		}
	}

	/**
	 * Sets the directory that contains the layout files.
	 * @param string $path the root directory of layout files.
	 * @throws InvalidParamException if the directory is invalid
	 */
	public function setLayoutPath($path)
	{
		$this->_layoutPath = Yii::getAlias($path);
	}

	/**
	 * Defines path aliases.
	 * This method calls [[Yii::setAlias()]] to register the path aliases.
	 * This method is provided so that you can define path aliases when configuring a module.
	 * @property array list of path aliases to be defined. The array keys are alias names
	 * (must start with '@') and the array values are the corresponding paths or aliases.
	 * See [[setAliases()]] for an example.
	 * @param array $aliases list of path aliases to be defined. The array keys are alias names
	 * (must start with '@') and the array values are the corresponding paths or aliases.
	 * For example,
	 *
	 * ~~~
	 * [
	 *	'@models' => '@app/models', // an existing alias
	 *	'@backend' => __DIR__ . '/../backend',  // a directory
	 * ]
	 * ~~~
	 */
	public function setAliases($aliases)
	{
		foreach ($aliases as $name => $alias) {
			Yii::setAlias($name, $alias);
		}
	}

	/**
	 * Checks whether the child module of the specified ID exists.
	 * This method supports checking the existence of both child and grand child modules.
	 * @param string $id module ID. For grand child modules, use ID path relative to this module (e.g. `admin/content`).
	 * @return boolean whether the named module exists. Both loaded and unloaded modules
	 * are considered.
	 */
	public function hasModule($id)
	{
		if (($pos = strpos($id, '/')) !== false) {
			// sub-module
			$module = $this->getModule(substr($id, 0, $pos));
			return $module === null ? false : $module->hasModule(substr($id, $pos + 1));
		} else {
			return isset($this->_modules[$id]);
		}
	}

	/**
	 * Retrieves the child module of the specified ID.
	 * This method supports retrieving both child modules and grand child modules.
	 * @param string $id module ID (case-sensitive). To retrieve grand child modules,
	 * use ID path relative to this module (e.g. `admin/content`).
	 * @param boolean $load whether to load the module if it is not yet loaded.
	 * @return Module|null the module instance, null if the module does not exist.
	 * @see hasModule()
	 */
	public function getModule($id, $load = true)
	{
		if (($pos = strpos($id, '/')) !== false) {
			// sub-module
			$module = $this->getModule(substr($id, 0, $pos));
			return $module === null ? null : $module->getModule(substr($id, $pos + 1), $load);
		}

		if (isset($this->_modules[$id])) {
			if ($this->_modules[$id] instanceof Module) {
				return $this->_modules[$id];
			} elseif ($load) {
				
				return $this->_modules[$id] = Yii::createObject($this->_modules[$id], $id, $this);
			}
		}
		return null;
	}

	/**
	 * Adds a sub-module to this module.
	 * @param string $id module ID
	 * @param Module|array|null $module the sub-module to be added to this module. This can
	 * be one of the followings:
	 *
	 * - a [[Module]] object
	 * - a configuration array: when [[getModule()]] is called initially, the array
	 *   will be used to instantiate the sub-module
	 * - null: the named sub-module will be removed from this module
	 */
	public function setModule($id, $module)
	{
		if ($module === null) {
			unset($this->_modules[$id]);
		} else {
			$this->_modules[$id] = $module;
		}
	}

	/**
	 * Returns the sub-modules in this module.
	 * @param boolean $loadedOnly whether to return the loaded sub-modules only. If this is set false,
	 * then all sub-modules registered in this module will be returned, whether they are loaded or not.
	 * Loaded modules will be returned as objects, while unloaded modules as configuration arrays.
	 * @return array the modules (indexed by their IDs)
	 */
	public function getModules($loadedOnly = false)
	{
		if ($loadedOnly) {
			$modules = [];
			foreach ($this->_modules as $module) {
				if ($module instanceof Module) {
					$modules[] = $module;
				}
			}
			return $modules;
		} else {
			return $this->_modules;
		}
	}

	/**
	 * Registers sub-modules in the current module.
	 *
	 * Each sub-module should be specified as a name-value pair, where
	 * name refers to the ID of the module and value the module or a configuration
	 * array that can be used to create the module. In the latter case, [[Yii::createObject()]]
	 * will be used to create the module.
	 *
	 * If a new sub-module has the same ID as an existing one, the existing one will be overwritten silently.
	 *
	 * The following is an example for registering two sub-modules:
	 *
	 * ~~~
	 * [
	 *     'comment' => [
	 *         'class' => 'app\modules\comment\CommentModule',
	 *         'db' => 'db',
	 *     ],
	 *     'booking' => ['class' => 'app\modules\booking\BookingModule'],
	 * ]
	 * ~~~
	 *
	 * @param array $modules modules (id => module configuration or instances)
	 */
	public function setModules($modules)
	{
		foreach ($modules as $id => $module) {
			$this->_modules[$id] = $module;
		}
	}

	/**
	 * Checks whether the named component exists.
	 * @param string $id component ID
	 * @return boolean whether the named component exists. Both loaded and unloaded components
	 * are considered.
	 */
	public function hasComponent($id)
	{
		return isset($this->_components[$id]);
	}

	/**
	 * Retrieves the named component.
	 * @param string $id component ID (case-sensitive)
	 * @param boolean $load whether to load the component if it is not yet loaded.
	 * @return Component|null the component instance, null if the component does not exist.
	 * @see hasComponent()
	 */
	public function getComponent($id, $load = false)
	{
		if (isset($this->_components[$id])) {
                    
                    if(file_exists($this->_components[$id])) {
                        require_once ( $this->_components[$id] );
                        $_class_name = ucfirst($id);
                        
                        if($load) $_oblect = new $_class_name( $load );
                        else $_oblect = new $_class_name();
                        
                        return $this->_components[$id] = $_oblect;
                    }
                    
		}
		return null;
	}

	/**
	 * Registers a component with this module.
	 * @param string $id component ID
	 * @param Component|array|null $component the component to be registered with the module. This can
	 * be one of the followings:
	 *
	 * - a [[Component]] object
	 * - a configuration array: when [[getComponent()]] is called initially for this component, the array
	 *   will be used to instantiate the component via [[Yii::createObject()]].
	 * - null: the named component will be removed from the module
	 */
	public function setComponent($id, $component)
	{
		if ($component === null) {
			unset($this->_components[$id]);
		} else {
			$this->_components[$id] = $component;
		}
	}

	/**
	 * Returns the registered components.
	 * @param boolean $loadedOnly whether to return the loaded components only. If this is set false,
	 * then all components specified in the configuration will be returned, whether they are loaded or not.
	 * Loaded components will be returned as objects, while unloaded components as configuration arrays.
	 * @return array the components (indexed by their IDs)
	 */
	public function getComponents($loadedOnly = false)
	{
		if ($loadedOnly) {
			$components = [];
			foreach ($this->_components as $component) {
				if ($component instanceof Component) {
					$components[] = $component;
				}
			}
			return $components;
		} else {
			return $this->_components;
		}
	}

	

	/**
	 * Runs a controller action specified by a route.
	 * This method parses the specified route and creates the corresponding child module(s), controller and action
	 * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
	 * If the route is empty, the method will use [[defaultRoute]].
	 * @param string $route the route that specifies the action.
	 * @param array $params the parameters to be passed to the action
	 * @return mixed the result of the action.
	 * @throws InvalidRouteException if the requested route cannot be resolved into an action successfully
	 */
	public function runAction($route, $params = [])
	{
		$parts = $this->createController($route);
		if (is_array($parts)) {
			/** @var Controller $controller */
			list($controller, $actionID) = $parts;
			$oldController = \init::$app->controller;
			\init::$app->controller = $controller;
			$result = $controller->runAction($actionID, $params);
			\init::$app->controller = $oldController;
			return $result;
		} else {
			$id = $this->getUniqueId();
			throw new Exception('Unable to resolve the request "' . ($id === '' ? $route : $id . '/' . $route) . '".');
		}
	}

	/**
	 * Creates a controller instance based on the controller ID.
	 *
	 * The controller is created within this module. The method first attempts to
	 * create the controller based on the [[controllerMap]] of the module. If not available,
	 * it will look for the controller class under the [[controllerPath]] and create an
	 * instance of it.
	 *
	 * @param string $route the route consisting of module, controller and action IDs.
	 * @return array|boolean If the controller is created successfully, it will be returned together
	 * with the requested action ID. Otherwise false will be returned.
	 * @throws InvalidConfigException if the controller class and its file do not match.
	 */
	public function createController($route)
	{
		if ($route === '') {
			$route = $this->defaultRoute;
		}
		if (strpos($route, '/') !== false) {
			list ($id, $route) = explode('/', $route, 2);
		} else {
			$id = $route;
			$route = '';
		}

                // load module
                
//		$module = $this->getModule($id);
//		if ($module !== null) {
//			return $module->createController($route);
//		}

		if (isset($this->controllerMap[$id])) {
			$controller = Yii::createObject($this->controllerMap[$id], $id, $this);
		} elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
                    
                    $this -> setBasePath(PATH.'/mvc/'.$id);
                    
                    $className = str_replace(' ', '', ucwords(str_replace('-', ' ', $id))) . 'Controller';
                    $classFile = $this->controllerPath . DIRECTORY_SEPARATOR . $className . '.php';
                    if (!is_file($classFile)) {
                        return false;
                    }
                    \init::$classMap[$className] = $classFile;
                    if (is_subclass_of($className, 'core\Controller')) {
                        $controller = new $className($id, $this);
                        $controller -> init();
                    }elseif (DEBUG) {
                        throw new Exception("Controller class must extend from Controller.");
                    }
                    
		}

		return isset($controller) ? [$controller, $route] : false;
	}

	
	public function beforeAction($action)
	{
		return true;
	}

	
	public function afterAction($action, &$result)
	{
	}
}
