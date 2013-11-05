<?php

class Social_ControllerAdmin_Forum extends XFCP_Social_ControllerAdmin_Forum
{

	public function actionSave()
	{
		$forumWriterData = $this->_input->filter(array(
			'news_source' => XenForo_Input::UINT,
		));

		XenForo_Application::set('forumWriterData', $forumWriterData);

		return parent::actionSave();
	}
}