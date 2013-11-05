<?php

class Social_ViewPublic_Thread_View extends XFCP_Social_ViewPublic_Thread_View
{
	public function renderHtml()
	{
		parent::renderHtml();

		if (XenForo_Application::isRegistered('blogView')) {
			$firstPostId = $this->_params['firstPost']['post_id'];
			$this->_params['firstPost'] = $this->_params['posts'][$firstPostId];
			unset($this->_params['posts'][$firstPostId]);
			$commentTree = Social_ViewPublic_Helper_Comment::buildCommentTree($this->_params['posts']);
			$this->_params['commentsTemplate'] = Social_ViewPublic_Helper_Comment::createCommentsTemplateObject($this, $commentTree);
			$this->_params['posts'] = array($this->_params['firstPost']['post_id'] => $this->_params['firstPost']);
		}
	}
}