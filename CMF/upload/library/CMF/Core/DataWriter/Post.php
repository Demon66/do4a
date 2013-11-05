<?php

/**
 * Data writer for relations
 *
 * @package Relations
 */
class CMF_Core_DataWriter_Post extends XFCP_CMF_Core_DataWriter_Post
{
	protected function _messagePostSave()
	{
		parent::_messagePostSave();
		$this->_getPostModel()->deletePostCacheById($this->get('post_id'));
	}
	protected function _messagePostDelete()
	{
		parent::_messagePostSave();
		$this->_getPostModel()->deletePostCacheById($this->get('post_id'));
	}
}