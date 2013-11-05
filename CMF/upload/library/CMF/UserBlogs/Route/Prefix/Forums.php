<?php

class CMF_UserBlogs_Route_Prefix_Forums extends XFCP_CMF_UserBlogs_Route_Prefix_Forums
{

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$options = XenForo_Application::get('options');
		$action = XenForo_Link::getPageNumberAsAction($action, $extraParams);
		//for blog list
		if (is_array($data) && !empty($data['user_id']) && $data['node_id'] == 'user_blog_' . $data['user_id'] && $data['parent_node_id'] == $options->cmfUserBlogsNode)
		{
			$action = XenForo_Link::buildIntegerAndTitleUrlComponent($data['user_id'], $data['username']) . '/' . $action;
			$data['node_id'] = $data['parent_node_id'];
			$data['title'] = $data['userblogs_node_title'];
		}
		//for forum controller canonical link cmfUserBlogsRoutePrefix
		if (is_array($data) && !empty($data['blog_user_id']) && $data['node_id'] == $options->cmfUserBlogsNode)
		{
			$action = XenForo_Link::buildIntegerAndTitleUrlComponent($data['blog_user_id'], $data['blog_username']) . '/' . $action;
		}
		if ($options->cmfUserBlogsRoutePrefix && $data['node_id'] == $options->cmfUserBlogsNode)
		{
			return XenForo_Link::buildBasicLink('user-blogs', $action, $extension);
		}
		return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
	}


	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		if (sizeof(explode('/', $routePath)) > 2)
		{
			/** @var $action string */
			$action = $router->resolveActionWithIntegerOrStringParam($routePath, $request, 'node_id', 'node_name');
			$action = $router->resolveActionWithIntegerOrStringParam($action, $request, 'user_id', 'username');
			$action = $router->resolveActionAsPageNumber($action, $request);
			return $router->getRouteMatch('XenForo_ControllerPublic_Forum', $action, 'forums');
		}
		return parent::match($routePath, $request, $router);
	}
}