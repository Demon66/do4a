<?php

class Social_Helper_Node
{
	/**
	 * Used only to build cache since have extra query for getting options right from db not from registry
	 *
	 * @static
	 * @param $type
	 * @param null $rootId
	 * @return mixed
	 */
	public static function getTypeRoot($type, $rootId = null)
	{
		$optionName = 'social' . ucfirst($type) . 'Root';
		$options = XenForo_Model::create('XenForo_Model_Option')->buildOptionArray();
		$rootId = isset($options[$optionName]) ? $options[$optionName] : 0;
		$root = XenForo_Model::create('XenForo_Model_Node')->getNodeById($rootId);
		XenForo_Application::set($type . 'Root', $root);
		return $root;
	}

	public static function updateTypeCache($type)
	{
		$root = self::getTypeRoot($type);
        if(empty($root)) {
            return;
        }

		XenForo_Application::setSimpleCacheData($type . 'Root', $root);

        $nodes = XenForo_Model::create('XenForo_Model_Node')->getChildNodes($root);
        $nodeIds = !empty($nodes) ? array_keys($nodes) : array();
        $nodeIds[] = $root['node_id'];

        XenForo_Application::setSimpleCacheData($type . 'NodeIds', $nodeIds);
	}

	public static function getTypeRootFromCache($type)
	{
		$root = XenForo_Application::getSimpleCacheData($type . 'Root');
		return $root;
	}

    public static function getTypeNodeIdsFromCache($type)
   	{
   		$nodeIds = XenForo_Application::getSimpleCacheData($type . 'NodeIds');
   		return !empty($nodeIds) ? $nodeIds : array();
   	}

	public static function typeView($type, $data)
	{
        $optionName = 'social' . ucfirst($type) . 'Active';
        if(!XenForo_Application::getOptions()->get($optionName)){
            return false;
        }

		$nodeId = !empty($data['node_id']) ? $data['node_id'] : $data;

		return in_array($nodeId, self::getTypeNodeIdsFromCache($type));
	}
}