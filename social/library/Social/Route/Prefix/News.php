<?php

class Social_Route_Prefix_News implements XenForo_Route_Interface, XenForo_Route_BuilderInterface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		return $router->getRouteMatch('Social_ControllerPublic_News', 'index', 'news', (XenForo_Application::get('options')->socialNewsIndex) ? 'index' : $routePath);
	}

	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		if (XenForo_Application::get('options')->socialNewsIndex && $outputPrefix === 'news' && $action === '' && $extension === '') {
			return XenForo_Link::buildBasicLink('index', $action, $extension);
		}
		else
		{
			return XenForo_Link::buildBasicLink('news', $action, $extension);
		}
	}
}