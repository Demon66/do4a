<?php

class Social_Widget_SharePage extends Social_Widget_Abstract
{

	protected $_widget = array(
		'templateName' => 'social_sidebar_share_page'
	);

	protected function _constructSetup(array &$viewParams = array())
	{
		$requestPaths = XenForo_Application::get('requestPaths');
		$this->_widget['params']['url'] = $requestPaths['requestUri'];
	}

}