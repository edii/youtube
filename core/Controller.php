<?php
namespace core;

use init;
use core\actions\InlineAction as InlineAction;

class Controller extends Component {
        public $id;
	/**
	 * @var Module $module the module that this controller belongs to.
	 */
	public $module;
	/**
	 * @var string the ID of the action that is used when the action ID is not specified
	 * in the request. Defaults to 'index'.
	 */
	public $defaultAction = 'index';
	/**
	 * @var string|boolean the name of the layout to be applied to this controller's views.
	 * This property mainly affects the behavior of [[render()]].
	 * Defaults to null, meaning the actual layout value should inherit that from [[module]]'s layout value.
	 * If false, no layout will be applied.
	 */
	public $layout;
	/**
	 * @var Action the action that is currently being executed. This property will be set
	 * by [[run()]] when it is called by [[Application]] to run an action.
	 */
	public $action;
	/**
	 * @var View the view object that can be used to render views or view files.
	 */
	private $_view;


	/**
	 * @param string $id the ID of this controller.
	 * @param Module $module the module that this controller belongs to.
	 * @param array $config name-value pairs that will be used to initialize the object properties.
	 */
	public function __construct($id, $module, $config = [])
	{
		$this->id = $id;
		$this->module = $module;
		//parent::__construct($config);
	}

	/**
	 * Declares external actions for the controller.
	 * This method is meant to be overwritten to declare external actions for the controller.
	 * It should return an array, with array keys being action IDs, and array values the corresponding
	 * action class names or action configuration arrays. For example,
	 */
	public function actions()
	{
		return [];
	}

	/**
	 * Runs an action within this controller with the specified action ID and parameters.
	 * If the action ID is empty, the method will use [[defaultAction]].
	 * @param string $id the ID of the action to be executed.
	 * @param array $params the parameters (name-value pairs) to be passed to the action.
	 * @return mixed the result of the action.
	 * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
	 * @see createAction()
	 */
	public function runAction($id, $params = [])
	{
            $action = $this->createAction($id);
            if ($action !== null) {
                if (\init::$app->requestedAction === null) {
                        \init::$app->requestedAction = $action;
                }
                $oldAction = $this->action;
                $this->action = $action;
                $result = null;
                // $event = new ActionEvent($action);
                if ($this->module->beforeAction($action) && $this->beforeAction($action)) {
                        $result = $action->runWithParams($params);
                        $this->afterAction($action, $result);
                        $this->module->afterAction($action, $result);
                        //$event = new ActionEvent($action);
                        //$event->result = $result;
                }
                $this->action = $oldAction;
                return $result;
            }else {
                    throw new Exception('Unable to resolve the request: ' . $this->getUniqueId() . '/' . $id);
            }
            
	}

	/**
	 * Runs a request specified in terms of a route.
	 * @param string $route the route to be handled, e.g., 'view', 'comment/view', '/admin/comment/view'.
	 * @param array $params the parameters to be passed to the action.
	 * @return mixed the result of the action.
	 * @see runAction()
	 */
	public function run($route, $params = [])
	{
		$pos = strpos($route, '/');
		if ($pos === false) {
			return $this->runAction($route, $params);
		} elseif ($pos > 0) {
			return $this->module->runAction($route, $params);
		} else {
			return \init::$app->runAction(ltrim($route, '/'), $params);
		}
	}

	/**
	 * Binds the parameters to the action.
	 */
	public function bindActionParams($action, $params)
	{
		return [];
	}

	/**
	 * Creates an action based on the given action ID.
	 * @param string $id the action ID.
	 * @return Action the newly created action instance. Null if the ID doesn't resolve into any action.
	 */
	public function createAction($id)
	{
		if ($id === '') {
			$id = $this->defaultAction;
		}

		$actionMap = $this->actions();
		if (isset($actionMap[$id])) {
			return Yii::createObject($actionMap[$id], $id, $this);
		} elseif (preg_match('/^[a-z0-9\\-_]+$/', $id) && strpos($id, '--') === false && trim($id, '-') === $id) {
                        $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
                        
			$methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $id))));
			if (method_exists($this, $methodName)) {
				$method = new \ReflectionMethod($this, $methodName);
				if ($method->getName() === $methodName) {
					return new InlineAction($this, $id, $methodName);
				}
			}
		}
		return null;
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters).
	 * You may override this method to do last-minute preparation for the action.
	 * If you override this method, please make sure you call the parent implementation first.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		return true;
	}

	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * If you override this method, please make sure you call the parent implementation first.
	 * @param Action $action the action just executed.
	 * @param mixed $result the action return result.
	 */
	public function afterAction($action, $result)
	{
		//$event = new ActionEvent($action);
		//$event->result = $result;
		//$this->trigger(self::EVENT_AFTER_ACTION, $event);
	}

	/**
	 * @return string the controller ID that is prefixed with the module ID (if any).
	 */
	public function getUniqueId()
	{
		return $this->module instanceof Application ? $this->id : $this->module->getUniqueId() . '/' . $this->id;
	}

	/**
	 * Returns the route of the current request.
	 * @return string the route (module ID, controller ID and action ID) of the current request.
	 */
	public function getRoute()
	{
		return $this->action !== null ? $this->action->getUniqueId() : $this->getUniqueId();
	}

	/**
	 * Renders a view and applies layout if available.
	 * @param string $view the view name. Please refer to [[findViewFile()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * These parameters will not be available in the layout.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file or the layout file does not exist.
	 */
	public function render($view, $params = [])
	{
		$output = $this->getView()->render($view, $params, $this);
		$layoutFile = $this->findLayoutFile( PATH.'layout/' );
                
		if ($layoutFile !== false) {
                        
			return $this->getView()->renderFile($layoutFile, ['content' => $output], $this);
		} else {
			return $output;
		}
                
                
	}

	/**
	 * Renders a view.
	 * This method differs from [[render()]] in that it does not apply any layout.
	 * @param string $view the view name. Please refer to [[render()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderPartial($view, $params = [])
	{
		return $this->getView()->render($view, $params, $this);
	}

	/**
	 * Renders a view file.
	 * @param string $file the view file to be rendered. This can be either a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderFile($file, $params = [])
	{
		return $this->getView()->renderFile($file, $params, $this);
	}

	/**
	 * Returns the view object that can be used to render views or view files.
	 * The [[render()]], [[renderPartial()]] and [[renderFile()]] methods will use
	 * this view object to implement the actual view rendering.
	 * If not set, it will default to the "view" application component.
	 * @return View the view object that can be used to render views or view files.
	 */
	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = \init::$app->getView();
		}
		return $this->_view;
	}

	/**
	 * Sets the view object to be used by this controller.
	 * @param View $view the view object that can be used to render views or view files.
	 */
	public function setView($view)
	{
		$this->_view = $view;
	}

	/**
	 * Returns the directory containing view files for this controller.
	 * The default implementation returns the directory named as controller [[id]] under the [[module]]'s
	 * [[viewPath]] directory.
	 * @return string the directory containing the view files for this controller.
	 */
	public function getViewPath()
	{
		return $this->module->getViewPath() . DIRECTORY_SEPARATOR . $this->id;
	}

	/**
	 * Finds the view file based on the given view name.
	 * @param string $view the view name or the path alias of the view file. Please refer to [[render()]]
	 * on how to specify this parameter.
	 * @return string the view file path. Note that the file may not exist.
	 */
	public function findViewFile($view)
	{
		return $this->getViewPath() . DIRECTORY_SEPARATOR . $view;
	}

	/**
	 * Finds the applicable layout file.
	 * @param View $view the view object to render the layout file.
	 * @return string|boolean the layout file path, or false if layout is not needed.
	 * Please refer to [[render()]] on how to specify this parameter.
	 * @throws InvalidParamException if an invalid path alias is used to specify the layout.
	 */
	protected function findLayoutFile($view)
	{
		$module = $this->module;
		if (is_string($this->layout)) {
			$layout = $this->layout;
		} elseif ($this->layout === null) {
			while ($module !== null && $module->layout === null) {
				$module = $module->module;
			}
			if ($module !== null && is_string($module->layout)) {
				$layout = $module->layout;
			}
		}

		if (!isset($layout)) {
			return false;
		}

		if (strncmp($layout, '/', 1) === 0) {
			$file = \init::$app->getLayoutPath() . DIRECTORY_SEPARATOR . substr($layout, 1);
		} else {
			$file = $module->getLayoutPath() . DIRECTORY_SEPARATOR . $layout;
                        
		}

                
		if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
			return $file;
		}
		$path = $file . '.php';
		if (!is_file($path)) {
			$path = $file . '.php';
		}
		return $path;
	}
}
