<?php

/**
 * Data writer for posts.
 *
 * @package XenForo_Discussion
 */
class Social_DataWriter_DiscussionMessage_Post extends XFCP_Social_DataWriter_DiscussionMessage_Post
{

	protected function _getFields()
	{
		$fields = parent::_getFields();
		$fields['xf_post']['parent_post_id'] = array('type' => self::TYPE_UINT, 'default' => 0);
		return $fields;
	}

	protected function _messagePreSave()
	{
		if (XenForo_Application::isRegistered('postWriterData')) {
			$this->bulkSet(XenForo_Application::get('postWriterData'));
		}

		parent::_messagePreSave();
	}
}