<?php

class CMF_Core_Install
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
					CREATE TABLE IF NOT EXISTS cmf_post_cache (
						post_id INT UNSIGNED NOT NULL,
						post_cache MEDIUMTEXT NOT NULL,
						cache_type VARCHAR(25) NOT NULL DEFAULT 'thread',
						cache_date INT UNSIGNED NOT NULL,
						cache_filters VARCHAR(255) NOT NULL DEFAULT '',
						PRIMARY KEY (post_id, cache_type)
					) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
			");
			$db->query("
					CREATE TABLE IF NOT EXISTS cmf_tag (
						tag_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
						tag VARCHAR(50) NOT NULL DEFAULT '',
						tag_count INT UNSIGNED NOT NULL,
						PRIMARY KEY (tag_id),
						UNIQUE KEY tag (tag),
						KEY tag_count (tag_count)
					) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
			");
			$db->query("
					CREATE TABLE IF NOT EXISTS cmf_thread (
						thread_id INT UNSIGNED NOT NULL,
						PRIMARY KEY (thread_id)
					) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
			");
			//TODO add index
			$db->query("
					CREATE TABLE IF NOT EXISTS cmf_relation (
						content_id INT UNSIGNED NOT NULL,
						content_type VARCHAR(25) NOT NULL DEFAULT 'thread',
						target_content_id INT UNSIGNED NOT NULL,
						target_content_type VARCHAR(25) NOT NULL DEFAULT 'thread'
					) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci
			");
		}
	}
	public static function uninstall()
	{
		/** @var $db Zend_Db_Adapter_Abstract */
		$db = XenForo_Application::get('db');

		$db->query("
				DROP TABLE IF EXISTS cmf_post_cache
		");
		$db->query("
				DROP TABLE IF EXISTS cmf_tag
		");
		$db->query("
				DROP TABLE IF EXISTS cmf_thread
		");
		$db->query("
				DROP TABLE IF EXISTS cmf_relation
		");
	}
}