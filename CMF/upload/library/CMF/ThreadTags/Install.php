<?php

class CMF_ThreadTags_Install
{
	public static function install($existingAddOn, $addOnData)
	{
		if (XenForo_Application::$versionId < 1010170)
		{
		        throw new XenForo_Exception('This Add-On requires XenForo version 1.1.1 or higher.');
		}
		if (!$existingAddOn) // first install
		{
			/** @var $db Zend_Db_Adapter_Abstract */
			$db = XenForo_Application::get('db');

			$db->query("
					ALTER TABLE cmf_thread
						ADD tags VARCHAR(255) NOT NULL DEFAULT '' AFTER thread_id
	        ");
		}
	}
	public static function uninstall()
	{
		/** @var $db Zend_Db_Adapter_Abstract */
		$db = XenForo_Application::get('db');

		$db->query("
				ALTER TABLE cmf_thread
					DROP tags
        ");
	}
}