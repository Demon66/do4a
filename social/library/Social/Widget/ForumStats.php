<?php

class Social_Widget_ForumStats extends Social_Widget_Abstract
{

	protected $_widget = array(
		'templateName' => 'social_sidebar_forum_stats'
	);

	/**
	 * 1 query if boardInfo is in DataRegistry
	 * 5-6 queries otherwise (has to rebuild it)
	 *
	 * @return array
	 */
	protected function _constructSetup(array &$viewParams = array())
	{

		// 1 query
		$boardTotals = Social_Sidebar::$controller->getModelFromCache('XenForo_Model_DataRegistry')->get('boardTotals');
		if (!$boardTotals) {
			// 4-5 queries
			$boardTotals = Social_Sidebar::$controller->getModelFromCache('XenForo_Model_Counters')->rebuildBoardTotalsCounter();
		}

		$this->_widget['params']['boardTotals'] = $boardTotals;
	}
}