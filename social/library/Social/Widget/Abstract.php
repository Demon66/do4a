<?php

abstract class Social_Widget_Abstract implements ArrayAccess
{

	protected $_widget = array();

	public function __construct(array &$viewParams = array(), array $options = array())
	{
		$this->_widget = array_merge($this->_widget, $options);
		$this->_widget['params'] = $viewParams;
		$this->_constructSetup($viewParams);
	}

	abstract protected function _constructSetup(array &$viewParams = array());


	/**
	 * @return Social_Widget_Abstract
	 */
	public static function create($class, array &$viewParams = array(), array $options = array())
	{
		return new $class($viewParams, $options);
	}

	public function save()
	{
		Social_Sidebar::$widgets[get_class($this)] = $this;
	}

	/**
	 * OO approach to getting a value from the visitor. Good if you want a single value in one line.
	 *
	 * @param string $name
	 *
	 * @return mixed False if the value can't be found
	 */
	public function get($name)
	{
		if (array_key_exists($name, $this->_widget)) {
			return $this->_widget[$name];
		}
		else
		{
			return false;
		}
	}

	/**
	 * For ArrayAccess.
	 *
	 * @param string $offset
	 */
	public function offsetExists($offset)
	{
		return isset($this->_widget[$offset]);
	}

	/**
	 * For ArrayAccess.
	 *
	 * @param string $offset
	 */
	public function offsetGet($offset)
	{
		return $this->_widget[$offset];
	}

	/**
	 * For ArrayAccess.
	 *
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		$this->_widget[$offset] = $value;
	}

	/**
	 * For ArrayAccess.
	 *
	 * @param string $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->_widget[$offset]);
	}

	/**
	 * Magic method for array access
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

}