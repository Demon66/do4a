<?php

class Social_ControllerPublic_Misc extends XFCP_Social_ControllerPublic_Misc
{

	/**
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionCreateThreadBlogChooser()
	{
		/* @var $response XenForo_ControllerResponse_View*/
		$response = $this->actionBlogChooser();
		if ($response instanceof XenForo_ControllerResponse_View) {
			$response->params['action'] = 'create-thread';
		}
		return $response;
	}

	/**
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionBlogChooser()
	{
		$route = $this->_input->filterSingle('route', XenForo_Input::STRING);

		/* @var $nodeModel XenForo_Model_Node */
		$nodeModel = $this->getModelFromCache('XenForo_Model_Node');
		$nodesViewable = $nodes = $nodeModel->getViewableNodeList(null, true);
		$nodes = array();
		foreach ($nodesViewable as $nodeViewable)
		{
			if (Social_Helper_Node::typeView('blog', $nodeViewable)) {
				$nodes[$nodeViewable['node_id']] = $nodeViewable;
			}
		}

		$nodeTypes = $nodeModel->getAllNodeTypes();

		$viewParams = array(
			'route' => $route,
			'nodes' => $nodes,
			'nodeTypes' => $nodeTypes,
		);

		return $this->responseView('Social_ViewPublic_NewsForums', 'social_node_chooser', $viewParams);
	}

	/**
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionNewsForumChooser()
	{
		$route = $this->_input->filterSingle('route', XenForo_Input::STRING);

		/* @var $forumModel XenForo_Model_Forum */
		$forumModel = $this->getModelFromCache('XenForo_Model_Forum');
		$nodes = $forumModel->getForums(array('news_source' => 1));

		/* @var $nodeModel XenForo_Model_Node */
		$nodeModel = $this->getModelFromCache('XenForo_Model_Node');
		$nodeTypes = $nodeModel->getAllNodeTypes();

		foreach ($nodes as &$node)
		{
			$node['depth'] = 0;
		}

		$viewParams = array(
			'route' => $route,
			'nodes' => $nodes,
			'nodeTypes' => $nodeTypes,
		);

		return $this->responseView('Social_ViewPublic_NewsForums', 'social_node_chooser', $viewParams);
	}

	/**
	 * Provides data to build the site jump menu (forum jump etc.)
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionQuickNavigationMenu()
	{
		$route = $this->_input->filterSingle('route', XenForo_Input::STRING);

		/* @var $nodeModel XenForo_Model_Node */
		$nodeModel = $this->getModelFromCache('XenForo_Model_Node');

		$nodes = $nodeModel->getViewableNodeList(null, true);
		$nodeTypes = $nodeModel->getAllNodeTypes();

		$quickNavMenuNodeTypes = XenForo_Application::get('options')->quickNavMenuNodeTypes;

		if (!isset($nodeTypes['_all']) && !in_array('_all', $quickNavMenuNodeTypes)) {
			$nodes = $nodeModel->filterNodeTypesInTree($nodes, $quickNavMenuNodeTypes);
		}

		$nodes = $nodeModel->filterOrphanNodes($nodes);

		$selected = preg_replace('/[^a-z0-9_-]/i', '', $this->_input->filterSingle('selected', XenForo_Input::STRING));

		$options = XenForo_Application::get('options');

		$viewParams = array(
			'route' => $route,
			'nodes' => $nodes,
			'nodeTypes' => $nodeTypes,
			'selected' => $selected,

			'homeLink' => ($options->homePageUrl ? $options->homePageUrl : false)
		);

		return $this->responseView('XenForo_ViewPublic_Misc_QuickNavigationMenu', 'quick_navigation_menu', $viewParams);
	}

}