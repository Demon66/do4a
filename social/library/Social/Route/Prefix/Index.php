<?php

class Social_Route_Prefix_Index extends XFCP_Social_Route_Prefix_Index
{

	/**
	 * Match a specific route for an already matched prefix.
	 *
	 * @see XenForo_Route_Interface::match()
	 */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		// this will make index go to News instead of the default index
		// is this behavior necessary? one could just update in the options that the defaault index is news
		return $router->getRouteMatch('Social_ControllerPublic_News', 'index', 'news', $routePath);
	}

}