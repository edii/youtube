<?php
namespace core;

use init;

class Component
{
	/**
	 * @var array the attached event handlers 
	 */
	private $_events = [];
	/**
	 * @var Behavior[] the attached behaviors
	 */
	private $_behaviors;

	/**
	 * Returns the value of a component property
	 * @see __set()
	 */
	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter();
		} else {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canGetProperty($name)) {
					return $behavior->$name;
				}
			}
		}
		if (method_exists($this, 'set' . $name)) {
			throw new Exception('Getting write-only property: ' . get_class($this) . '::' . $name);
		} else {
			throw new Exception('Getting unknown property: ' . get_class($this) . '::' . $name);
		}
	}

	/**
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value
	 * @throws UnknownPropertyException if the property is not defined
	 * @throws InvalidCallException if the property is read-only.
	 * @see __get()
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter($value);
			return;
		} elseif (strncmp($name, 'on ', 3) === 0) {
			$this->on(trim(substr($name, 3)), $value);
			return;
		} elseif (strncmp($name, 'as ', 3) === 0) {
			$name = trim(substr($name, 3));
			$this->attachBehavior($name, $value instanceof Behavior ? $value : Yii::createObject($value));
			return;
		} else {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canSetProperty($name)) {
					$behavior->$name = $value;
					return;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new Exception('Setting read-only property: ' . get_class($this) . '::' . $name);
		} else {
			throw new Exception('Setting unknown property: ' . get_class($this) . '::' . $name);
		}
	}

	/**
	 * Checks if a property value is null.
	 * @param string $name the property name or the event name
	 * @return boolean whether the named property is null
	 */
	public function __isset($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter() !== null;
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canGetProperty($name)) {
					return $behavior->$name !== null;
				}
			}
		}
		return false;
	}

	/**
	 * Sets a component property to be null.
	 * @param string $name the property name
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter(null);
			return;
		} else {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canSetProperty($name)) {
					$behavior->$name = null;
					return;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new Exception('Unsetting read-only property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * @param string $name the method name
	 * @param array $params method parameters
	 * @return mixed the method return value
	 */
	public function __call($name, $params)
	{
		$this->ensureBehaviors();
		foreach ($this->_behaviors as $object) {
			if ($object->hasMethod($name)) {
				return call_user_func_array([$object, $name], $params);
			}
		}

		throw new Exception('Calling unknown method: ' . get_class($this) . "::$name()");
	}

	/**
	 * This method is called after the object is created by cloning an existing one.
	 * It removes all behaviors because they are attached to the old object.
	 */
	public function __clone()
	{
		$this->_events = [];
		$this->_behaviors = null;
	}

	/**
	 * @param string $name the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
	 * @return boolean whether the property is defined
	 */
	public function hasProperty($name, $checkVars = true, $checkBehaviors = true)
	{
		return $this->canGetProperty($name, $checkVars, $checkBehaviors) || $this->canSetProperty($name, false, $checkBehaviors);
	}

	/**
	 * @param string $name the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
	 * @return boolean whether the property can be read
	 */
	public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
	{
		if (method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name)) {
			return true;
		} elseif ($checkBehaviors) {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canGetProperty($name, $checkVars)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param string $name the property name
	 * @param boolean $checkVars whether to treat member variables as properties
	 * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
	 * @return boolean whether the property can be written
	 */
	public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
	{
		if (method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name)) {
			return true;
		} elseif ($checkBehaviors) {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canSetProperty($name, $checkVars)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param string $name the property name
	 * @param boolean $checkBehaviors whether to treat behaviors' methods as methods of this component
	 * @return boolean whether the property is defined
	 */
	public function hasMethod($name, $checkBehaviors = true)
	{
		if (method_exists($this, $name)) {
			return true;
		} elseif ($checkBehaviors) {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->hasMethod($name)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @return array the behavior configurations.
	 */
	public function behaviors()
	{
		return [];
	}

	/**
	 * @param string $name the event name
	 * @return boolean whether there is any handler attached to the event.
	 */
	public function hasEventHandlers($name)
	{
		$this->ensureBehaviors();
		return !empty($this->_events[$name]) || Event::hasHandlers($this, $name);
	}

	/**
	 * @param string $name the event name
	 * @param callback $handler the event handler
	 * @param mixed $data the data to be passed to the event handler when the event is triggered.
	 */
	public function on($name, $handler, $data = null)
	{
		$this->ensureBehaviors();
		$this->_events[$name][] = [$handler, $data];
	}

	/**
	 * @param string $name event name
	 * @param callback $handler the event handler to be removed.
	 * @return boolean if a handler is found and detached
	 */
	public function off($name, $handler = null)
	{
		$this->ensureBehaviors();
		if (empty($this->_events[$name])) {
			return false;
		}
		if ($handler === null) {
			unset($this->_events[$name]);
			return true;
		} else {
			$removed = false;
			foreach ($this->_events[$name] as $i => $event) {
				if ($event[0] === $handler) {
					unset($this->_events[$name][$i]);
					$removed = true;
				}
			}
			if ($removed) {
				$this->_events[$name] = array_values($this->_events[$name]);
			}
			return $removed;
		}
	}

	/**
	 * @param string $name the event name
	 * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
	 */
	public function trigger($name, Event $event = null)
	{
		$this->ensureBehaviors();
		if (!empty($this->_events[$name])) {
			if ($event === null) {
				$event = new Event;
			}
			if ($event->sender === null) {
				$event->sender = $this;
			}
			$event->handled = false;
			$event->name = $name;
			foreach ($this->_events[$name] as $handler) {
				$event->data = $handler[1];
				call_user_func($handler[0], $event);
				if ($event->handled) {
					return;
				}
			}
		}
	}

	/**
	 * @param string $name the behavior name
	 * @return Behavior the behavior object, or null if the behavior does not exist
	 */
	public function getBehavior($name)
	{
		$this->ensureBehaviors();
		return isset($this->_behaviors[$name]) ? $this->_behaviors[$name] : null;
	}

	/**
	 * @return Behavior[] list of behaviors attached to this component
	 */
	public function getBehaviors()
	{
		$this->ensureBehaviors();
		return $this->_behaviors;
	}

	/**
	 * @return Behavior the behavior object
	 */
	public function attachBehavior($name, $behavior)
	{
		$this->ensureBehaviors();
		return $this->attachBehaviorInternal($name, $behavior);
	}

	/**
	 * @param array $behaviors list of behaviors to be attached to the component
	 */
	public function attachBehaviors($behaviors)
	{
		$this->ensureBehaviors();
		foreach ($behaviors as $name => $behavior) {
			$this->attachBehaviorInternal($name, $behavior);
		}
	}

	/**
	 * @param string $name the behavior's name.
	 * @return Behavior the detached behavior. Null if the behavior does not exist.
	 */
	public function detachBehavior($name)
	{
		$this->ensureBehaviors();
		if (isset($this->_behaviors[$name])) {
			$behavior = $this->_behaviors[$name];
			unset($this->_behaviors[$name]);
			$behavior->detach();
			return $behavior;
		} else {
			return null;
		}
	}

	/**
	 * Detaches all behaviors from the component.
	 */
	public function detachBehaviors()
	{
		$this->ensureBehaviors();
		foreach ($this->_behaviors as $name => $behavior) {
			$this->detachBehavior($name);
		}
	}

	/**
	 * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
	 */
	public function ensureBehaviors()
	{
		if ($this->_behaviors === null) {
			$this->_behaviors = [];
			foreach ($this->behaviors() as $name => $behavior) {
				$this->attachBehaviorInternal($name, $behavior);
			}
		}
	}

	/**
	 * @param string $name the name of the behavior.
	 * @param string|array|Behavior $behavior the behavior to be attached
	 * @return Behavior the attached behavior.
	 */
	private function attachBehaviorInternal($name, $behavior)
	{
		if (!($behavior instanceof Behavior)) {
			$behavior = Yii::createObject($behavior);
		}
		if (isset($this->_behaviors[$name])) {
			$this->_behaviors[$name]->detach();
		}
		$behavior->attach($this);
		return $this->_behaviors[$name] = $behavior;
	}
}
