<?php

class Social_Model_Node extends XFCP_Social_Model_Node
{

	public function getNodeDataForListDisplay($parentNode, $displayDepth, array $nodePermissions = null)
	{
		if (!empty($parentNode['node_id']) && $parentNode['node_id'] == XenForo_Application::get('options')->socialBlogRoot) {
			$displayDepth = 0;
		}

		return parent::getNodeDataForListDisplay($parentNode, $displayDepth, $nodePermissions);
	}

	// TODO: cache
	public function getViewableNodeList(array $nodePermissions = null, $listView = false)
	{
		if ($nodePermissions === null && $listView === false && XenForo_Application::isRegistered('viewableNodes')) {
			return XenForo_Application::get('viewableNodes');
		}

		$viewableNodes = parent::getViewableNodeList($nodePermissions, $listView);

		if ($nodePermissions === null && $listView === false) {
			XenForo_Application::set('viewableNodes', $viewableNodes);
		}

		return $viewableNodes;
	}

	public function updateNestedSetInfo()
	{
		$nodes = parent::updateNestedSetInfo();

		Social_Helper_Node::updateTypeCache('blog');

		return $nodes;
	}

	/**
	 * Gets the bread crumb nodes for the specified node.
	 *
	 * @param array $node
	 * @param boolean $includeSelf If true, includes itself as the last entry
	 *
	 * @return array List of nodes that form bread crumbs, root down; [node id] => node info
	 */
	public function getNodeBreadCrumbs(array $node, $includeSelf = true)
	{
		$breadCrumbs=parent::getNodeBreadCrumbs($node, $includeSelf);
		$blogRoot = Social_Helper_Node::getTypeRootFromCache('blog');
		$diffFilter=array($blogRoot['parent_node_id'] => $blogRoot['parent_node_id'], $blogRoot['node_id'] => $blogRoot['node_id']);
		foreach ($breadCrumbs as $nodeId => $node) {
			if ($nodeId==$blogRoot['parent_node_id'] || $nodeId==$blogRoot['node_id']) {
				return array_diff_key($breadCrumbs,$diffFilter);
			}
			$diffFilter[$nodeId]=$nodeId;
		}
		return $breadCrumbs;
	}

}