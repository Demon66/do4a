<?php

/**
 *
 */
class Social_Manufacture
{
	private static $_instance;

	protected $_db;

	public static final function getInstance()
	{
		if (!self::$_instance) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

    /**
  	 * @return Zend_Db_Adapter_Abstract
  	 */
	protected function _getDb()
	{
		if ($this->_db === null) {
			$this->_db = XenForo_Application::getDb();
		}

		return $this->_db;
	}

	public static function build($existingAddOn, $addOnData)
	{
		if (XenForo_Application::$versionId <= 1000070) {
			throw new XenForo_Exception(new XenForo_Phrase('social_requires_minimum_xenforo_version', array('version' => '1.0.1')));
		}

		$startVersion = 1;
		$endVersion = $addOnData['version_id'];

		if ($existingAddOn) {
			$startVersion = $existingAddOn['version_id'] + 1;
		}

		$install = self::getInstance();

		for ($i = $startVersion; $i <= $endVersion; $i++)
		{
			$method = '_installVersion' . $i;

			if (method_exists($install, $method) === false) {
				continue;
			}

			$install->$method();
		}
	}

	protected function _installVersion1()
	{
        $db = $this->_getDb();

        $db->query("
            ALTER TABLE xf_post ADD parent_post_id INT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Social' AFTER post_id
        ");

        $db->query("
            ALTER TABLE xf_forum ADD news_source TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Social' AFTER find_new
        ");

        $db->query("
            ALTER TABLE xf_thread ADD promotion_date INT NOT NULL DEFAULT '0' AFTER sticky
        ");
	}

	public static function destroy()
	{
		$lastUninstallStep = 3;

		$uninstall = self::getInstance();

		for ($i = 1; $i <= $lastUninstallStep; $i++)
		{
			$method = '_uninstallStep' . $i;

			if (method_exists($uninstall, $method) === false) {
				continue;
			}

			$uninstall->$method();
		}
	}

	protected function _uninstallStep1()
	{
		$db = $this->_getDb();

		$db->query("ALTER TABLE xf_forum DROP news_source");
		$db->query("ALTER TABLE xf_post DROP parent_post_id");
        $db->query("ALTER TABLE xf_thread DROP promotion_date");
	}
}