<?php
/**
 * Data writer for Forums.
 *
 * @package XenForo_Forum
 */
class Social_DataWriter_Forum extends XFCP_Social_DataWriter_Forum
{

	protected function _getFields()
	{
		$fields = parent::_getFields();
		$fields['xf_forum']['news_source'] = array('type' => self::TYPE_UINT, 'default' => 0);

		return $fields;
	}

	protected function _preSave()
	{
		if (XenForo_Application::isRegistered('forumWriterData')) {
			$this->bulkSet(XenForo_Application::get('forumWriterData'));
		}

		parent::_preSave();
	}

}