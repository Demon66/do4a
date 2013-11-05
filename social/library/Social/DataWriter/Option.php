<?php

/**
 * Data writer for options.
 *
 * @package XenForo_Options
 */
class Social_DataWriter_Option extends XFCP_Social_DataWriter_Option
{

	/**
	 * Post-save handling.
	 */
	protected function _postSave()
	{
		parent::_postSave();

		Social_Helper_Node::updateTypeCache('blog');
	}

}