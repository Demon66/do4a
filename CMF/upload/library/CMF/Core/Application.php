<?php
class CMF_Core_Application
{

	protected $_tagTypes = array();
	protected $_data = array();
	public static $coreLoaded = false;

	public function get($class, $type)
	{
		if (self::$coreLoaded && $type && $class && isset($this->_data[$class][$type])) {
			return $this->_data[$class][$type];
		}
		return array();
	}

	public function set($class, $type, array $data)
	{
		if (self::$coreLoaded && $type && $class && $data)
		{
			if (!isset($this->_data[$class][$type])) {
				$this->_data[$class][$type]=array();
			}
			$this->_data[$class][$type] += $data;
		}
	}

	public function clear($class='', $type='')
	{
		if (!$class)
		{
			$this->_data=array();
		}
		else if (!$type)
		{
			$this->_data[$class]=array();
		}
		else
		{
			$this->_data[$class][$type]=array();
		}
	}

	public function getTagContentTypes()
	{
		return array_keys($this->_tagTypes);
	}

	public function getTagTypes()
	{
		return $this->_tagTypes;
	}

	/**
	 * @param array|string
	 *
	 * @return integer
	 * */
	public function setTagContentTypes($contentTypes = null)
	{
		if (self::$coreLoaded && !empty($contentTypes))
		{
			if (!is_array($contentTypes))
			{
				$contentTypes=array($contentTypes);
			};
			foreach ($contentTypes as $type)
			{
				$this->_tagTypes[$type] = $type;
			}
		}
		return sizeof($this->_tagTypes);
	}

	/**
	 * @return CMF_Core_Application

	 */
	public static function getCMF()
	{
		if (XenForo_Application::isRegistered('cmf'))
		{
			return XenForo_Application::get('cmf');
		}
		else
		{
			$cmf = new CMF_Core_Application();
			XenForo_Application::set('cmf', $cmf);
			return $cmf;
		}
	}
}
