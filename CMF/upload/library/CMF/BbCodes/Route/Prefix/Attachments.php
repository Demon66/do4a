<?php

class CMF_BbCodes_Route_Prefix_Attachments extends XFCP_CMF_BbCodes_Route_Prefix_Attachments
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{
		if (sizeof($routeArr=explode('/',$routePath))>2)
		{
			$filter=strtolower($routeArr[0]);
			if (!empty(CMF_BbCodes_Listener::$validFilters[$filter]))
			{
				$request->setParam('live_filter', $filter);
				unset($routeArr[0]);
				$routePath=implode('/',$routeArr);
				$action = $router->resolveActionWithIntegerParam($routePath, $request, 'attachment_id');
				if (!$action) $action='filter';
				return $router->getRouteMatch('XenForo_ControllerPublic_Attachment', $action);
			}
		}
		return parent::match($routePath, $request, $router);
	}
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		$_validFilters=CMF_BbCodes_Listener::$validFilters;
		if (!empty($data['live_filter']) && !empty($_validFilters[$data['live_filter']]))
		{
			if (!empty($_validFilters[$data['live_filter']][2]))
			{
				$tail = '/attachments/'. $data['live_filter'].'/' . floor($data['data_id'] / 1000)
					. "/$data[data_id]-$data[file_hash].jpg";
				$thumb_ok = true;
				if (true) //TODO maybe less often check
				{
					$filePath = XenForo_Helper_File::getExternalDataPath() . $tail;
					$thumb_ok = (file_exists($filePath) && is_readable($filePath));
				}
				if ($thumb_ok) return XenForo_Application::$externalDataPath . $tail ;
			}
			$outputPrefix .= '/'.$data['live_filter'];
		}
		return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
	}
}