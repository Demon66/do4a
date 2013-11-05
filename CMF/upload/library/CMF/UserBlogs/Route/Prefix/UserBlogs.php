<?php

/**
 *
 */
class CMF_UserBlogs_Route_Prefix_UserBlogs implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		if ($userBlogsNode = XenForo_Application::get('options')->cmfUserBlogsNode)
		{

			$action = (strpos($routePath, '/') > 0) ? $router->resolveActionWithIntegerOrStringParam($routePath, $request, 'user_id', 'username') : $routePath;
			$action = $router->resolveActionAsPageNumber($action, $request);
			$request->setParam('node_id', $userBlogsNode);
			return $router->getRouteMatch('XenForo_ControllerPublic_Forum', $action, 'forums'); //May be 'userblogs' section???
		}
		return $router->getRouteMatch('XenForo_ControllerPublic_Index', 'index', 'forums'); //May be 'userblogs' section???
	}
}