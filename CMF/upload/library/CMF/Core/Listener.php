<?php

class CMF_Core_Listener
{
	public static function loadClassModel($class, array &$extend)
	{
		switch ($class)
		{
			case 'XenForo_Model_Thread':
				$extend[] = 'CMF_Core_Model_Thread';
				break;

			case 'XenForo_Model_Post':
				$extend[] = 'CMF_Core_Model_Post';
				break;

			case 'XenForo_Model_Search':
				$extend[] = 'CMF_Core_Model_Search';
				break;
		}
	}
	public static function loadClassController($class, array &$extend)
	{
		switch ($class)
		{
			case 'XenForo_ControllerPublic_Thread':
				$extend[] = 'CMF_Core_ControllerPublic_Thread';
				break;

			case 'XenForo_ControllerPublic_Forum':
				$extend[] = 'CMF_Core_ControllerPublic_Forum';
				break;

			case 'XenForo_ControllerPublic_Search':
				$extend[] = 'CMF_Core_ControllerPublic_Search';
				break;
		}
	}

	public static function loadClassDataWriter($class, array &$extend)
	{
		switch ($class)
		{
			case 'XenForo_DataWriter_DiscussionMessage_Post':
				$extend[] = 'CMF_Core_DataWriter_Post';
				break;

			case 'XenForo_DataWriter_Discussion_Thread':
				$extend[] = 'CMF_Core_DataWriter_Thread';
				break;
		}
	}

	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		/** @var Xenforo_Application $instance */
		$instance = Xenforo_Application::getInstance();
		$instance->addLazyLoader('cmf', array('CMF_Core_Listener', 'loadCMF'));
		XenForo_Template_Helper_Core::$helperCallbacks['tagshtml'] = array('CMF_Core_Template_Helper_Core', 'helperTagsHtml');
		XenForo_CacheRebuilder_Abstract::$builders['Tag']='CMF_Core_CacheRebuilder_Tag';
		CMF_Core_Application::$coreLoaded = true;
	}

	public static function searchSourceCreate(&$class)
	{
		if ($class=='XenForo_Search_SourceHandler_MySqlFt') {
			$class='CMF_Core_Search_SourceHandler_MySqlFt';
		}
	}

	public static function frontControllerPreView(XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
	{
		$options = XenForo_Application::get('options');
		if ($options->cmfTags && !CMF_Core_Application::getCMF()->getTagContentTypes())
		{
			$options->cmfTags = 0;
		}
	}

	/**
	 * @return CMF_Core_Application
	 */
	public static function loadCMF()
	{
		return new CMF_Core_Application();
	}

}