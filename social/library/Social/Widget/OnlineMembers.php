<?php

class Social_Widget_OnlineMembers extends Social_Widget_Abstract
{

	protected $_widget = array(
		'templateName' => 'social_sidebar_online_members'
	);

	/**
	 * 1 query
	 *
	 * @return array
	 */
	protected function _constructSetup(array &$viewParams = array())
	{
		if (empty($viewParams['onlineUsers'])) {
			$visitor = XenForo_Visitor::getInstance();

			/* @var $sessionModel XenForo_Model_Session*/
			$sessionModel = Social_Sidebar::$controller->getModelFromCache('XenForo_Model_Session');

			$viewParams['onlineUsers'] = $sessionModel->getSessionActivityQuickList(
				$visitor->toArray(),
				array('cutOff' => array('>', $sessionModel->getOnlineStatusTimeout())),
				($visitor['user_id'] ? $visitor->toArray() : null)
			);
		}

		$this->_widget['params']['onlineUsers'] = $viewParams['onlineUsers'];
	}

}