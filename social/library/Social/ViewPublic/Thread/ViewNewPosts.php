<?php

class Social_ViewPublic_Thread_ViewNewPosts extends XFCP_Social_ViewPublic_Thread_ViewNewPosts
{

	public function renderJson()
	{
		if (!XenForo_Application::isRegistered('blogView')) {
			return parent::renderJson();
		}

		$this->_templateName = '';
		$output = $this->_renderer->getDefaultOutputArray(get_class($this), $this->_params, $this->_templateName);
		$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
		$bbCodeOptions = array(
			'states' => array(
				'viewAttachments' => $this->_params['canViewAttachments']
			)
		);

		XenForo_ViewPublic_Helper_Message::bbCodeWrapMessages($this->_params['posts'], $bbCodeParser, $bbCodeOptions);

		$output['lastPost'] = $this->_params['lastPost'];
		$output['lastDate'] = $this->_params['lastPost']['post_date'];

		foreach ($this->_params['posts'] as & $post) {
			$params = $this->_params + array('post' => $post);
			$post['templateHtml'] = $this->createTemplateObject('post', $params)->render();
		}

		if ($this->_params['firstUnshownPost']) {
			$output['newMessagesNoticeHtml'] = $this->createTemplateObject(
				'thread_reply_new_posts',
				array('firstUnshownPost' => $this->_params['firstUnshownPost'], 'posts' => array())
			)->render();
		}

		$output['posts'] = $this->_params['posts'];
		$output['totalPosts'] = count($this->_params['posts']);

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}