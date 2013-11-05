<?php

/**
 *
 */
class Social_Route_Prefix_Blogs implements XenForo_Route_Interface
{
	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		$action = $router->resolveActionWithIntegerOrStringParam($routePath, $request, 'node_id', 'node_name');
		$action = $router->resolveActionAsPageNumber($action, $request);
		$blogRoot = Social_Helper_Node::getTypeRootFromCache('blog');

		$controllerName = 'XenForo_ControllerPublic_Forum';

		if (!$routePath)
        {
			if ($blogRoot['node_type_id'] == 'Category')
            {
				$controllerName = 'XenForo_ControllerPublic_Category';
			}
			$request->setParam('node_id', $blogRoot['node_id']);
		}

		return $router->getRouteMatch($controllerName, $action, 'blogs');
	}
}