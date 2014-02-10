<?php

namespace core\actions;

use init;
use core\Component as Component;

abstract class Action extends Component
{
	private $id;
	private $controller;

	/**
	 * Constructor.
	 * @param CController $controller the controller who owns this action.
	 * @param string $id id of the action.
	 */
	public function __construct($controller,$id)
	{
		$this->controller=$controller;
		$this->id=$id;
	}

	/**
	 * @return CController the controller who owns this action.
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * @return string id of this action
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Runs the action with the supplied request parameters.
	 * This method is internally called by {@link CController::runAction()}.
	 * @param array $params the request parameters (name=>value)
	 * @return boolean whether the request parameters are valid
	 * @since 1.1.7
	 */
	public function runWithParams($params)
	{
		$method=new \ReflectionMethod($this, 'run');
		if($method->getNumberOfParameters() > 0)
			return $this->runWithParamsInternal($this, $method, $params);
		else
			return $this->run();
	}

	/**
	 * Executes a method of an object with the supplied named parameters.
	 * This method is internally used.
	 * @param mixed $object the object whose method is to be executed
	 * @param ReflectionMethod $method the method reflection
	 * @param array $params the named parameters
	 * @return boolean whether the named parameters are valid
	 * @since 1.1.7
	 */
	protected function runWithParamsInternal($object, $method, $params)
	{
		$ps=array();
		foreach($method->getParameters() as $i=>$param)
		{
			$name=$param->getName();
			if(isset($params[$name]))
			{
				if($param->isArray())
					$ps[]=is_array($params[$name]) ? $params[$name] : array($params[$name]);
				elseif(!is_array($params[$name]))
					$ps[]=$params[$name];
				else
					return false;
			}
			elseif($param->isDefaultValueAvailable())
				$ps[]=$param->getDefaultValue();
			else
				return false;
		}
                
                // var_dump($object, $ps); die('action');
                
		$method->invokeArgs($object,$ps);
		return true;
	}
}
