<?php


class Social_EventListener_Static
{
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
        XenForo_Application::get('options')->socialNewsIndex = XenForo_Application::getOptions()->get('socialNewsActive') && XenForo_Application::getOptions()->get('socialNewsIndex');
		XenForo_Model_Import::$extraImporters += array('Social_Importer_Livestreet');

		
		// check if we need to change the default index route to news
		$options = XenForo_Application::getOptions();
		if ($options->socialNewsIndex)
		{
			$options->indexRoute = 'news/';
			XenForo_Link::setIndexRoute($options->indexRoute);
		}
		
		if (!$dependencies instanceof XenForo_Dependencies_Public) {
			return;
		}

		if (XenForo_Application::getOptions()->get('socialNewsActive')) {
			$data['routesPublic']['index']['build_link'] = 'all';
			XenForo_Link::setHandlerInfoForGroup('public', $data['routesPublic']);
		}
	}

	public static function controllerPreDispatch(XenForo_Controller $controller, $action)
	{
        Social_Sidebar::$controller=$controller;
	}
	
    public static function  frontControllerPreView(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
    {
        Social_Sidebar::setup($controllerResponse->controllerName, $controllerResponse->controllerAction, $containerParams['majorSection']);
    }
}