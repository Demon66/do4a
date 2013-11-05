<?php

class Social_ViewPublic_Thread_Reply extends XFCP_Social_ViewPublic_Thread_Reply
{
	public function renderHtml()
	{
		if (!XenForo_Application::isRegistered('blogView')) {
			parent::renderHtml();
			return;
		}

		$this->_templateName = 'social_thread_reply';
		$parentPostId = (isset($this->_params['post']['post_id'])) ? $this->_params['post']['post_id'] : 0;

		$this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
			$this, 'message', '',
			array('editorId' => 'message' . $parentPostId . '_' . substr(md5(microtime(true)), -8))
		);
	}

	public function renderJson()
	{
		if (!XenForo_Application::isRegistered('blogView')) {
			return parent::renderJson();
		}

		$this->_templateName = 'social_thread_reply';

		$output = $this->_renderer->getDefaultOutputArray(get_class($this), $this->_params, $this->_templateName);
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}