<?php

class Social_Widget_NewsNavigation extends Social_Widget_Abstract
{
	protected $_widget = array(
		'templateName' => 'social_sidebar_news_navigation'
	);

	/**
	 * 1 query if boardInfo is in DataRegistry
	 * 5-6 queries otherwise (has to rebuild it)
	 *
	 * @return array
	 */
	protected function _constructSetup(array &$viewParams = array())
	{
		$this->_widget['params']['newsNodes'] = !empty($viewParams['newsNodes']) ?
				$viewParams['newsNodes'] : Social_Sidebar::$controller->getModelFromCache('XenForo_Model_Forum')->getForums(array('news_source' => 1));

	}

}