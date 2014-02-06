<?php
namespace core;

use init;
/**
 * Перехватчик ошибок
 */

class Exception extends \Exception
{
	
	public function getName() {
		return 'Exception';
	}

	/**
	 * @return array the array representation of this object.
	 */
	public function toArray() {
		return $this->toArrayRecursive($this);
	}

	/**
	 * @param \Exception $exception object
	 * @return array 
	 */
	protected function toArrayRecursive($exception) {
		$array = [
			'type' => get_class($exception),
			'name' => $exception instanceof self ? $exception->getName() : 'Exception',
			'message' => $exception->getMessage(),
			'code' => $exception->getCode(),
		];
		if (($prev = $exception->getPrevious()) !== null) {
			$array['previous'] = $this->toArrayRecursive($prev);
		}
		return $array;
	}
}
