<?php

class Social_Widget_LatestThreads extends Social_Widget_Abstract
{
	protected $_widget = array(
		'titlePhraseName' => 'social_latest_forum_threads',
		'templateName' => 'social_sidebar_posts',
		'limit' => 10,
	);

	/**
	 * 1 query if boardInfo is in DataRegistry
	 * 5-6 queries otherwise (has to rebuild it)
	 *
	 * @return array
	 */
	protected function _constructSetup(array &$viewParams = array())
	{
        $visitor = XenForo_Visitor::getInstance();
		/* @var $threadModel XenForo_Model_Thread */
		$threadModel = Social_Sidebar::$controller->getModelFromCache('XenForo_Model_Thread');
		/* @var $nodeModel XenForo_Model_Node */
		$nodeModel = Social_Sidebar::$controller->getModelFromCache('XenForo_Model_Node');

		if (!empty($this->_widget['currentNode']) && !empty($viewParams['forum']['node_id'])) {
			$this->_widget['titlePhraseName'] .= '_from_current';
			$nodeIds = array($viewParams['forum']['node_id']);
		}
		else
		{
			$nodes = $nodeModel->getViewableNodeList();

			if (isset($this->_widget['type'])) {
				foreach ($nodes as $key => $node) {
					if (!Social_Helper_Node::typeView($this->_widget['type'], $node)) {
						unset($nodes[$key]);
					}
				}
			}
			else
			{
				foreach ($nodes as $key => $node) {
					if (Social_Helper_Node::typeView('blog', $node)) {
						unset($nodes[$key]);
					}
				}
			}
			$nodeIds = array_keys($nodes);
		}

		$threadConditions = array(
			'node_id' => !empty($nodeIds) ? $nodeIds : -1,
            'deleted' => false,
            'moderated' => false
		);

		$threadFetchOptions = array(
			'limit' => $this->_widget['limit'],
			'join' => XenForo_Model_Thread::FETCH_AVATAR,
			'order' => 'post_date',
			'orderDirection' => 'desc',
            'permissionCombinationId' => $visitor['permission_combination_id']
		);

		$threads = $threadModel->getThreads($threadConditions, $threadFetchOptions);

        foreach ($threads as $key => &$thread)
        {
              $thread['permissions'] = XenForo_Permission::unserializePermissions($thread['node_permission_cache']);

              if (!$threadModel->canViewThreadAndContainer($thread, $thread, $null, $thread['permissions']))
              {
                  unset($threads[$key]);
              }
        }

        $this->_widget['params']['showAvatar'] = isset($this->_widget['showAvatar']) ? $this->_widget['showAvatar'] : 0;
		$this->_widget['params']['posts'] = $threads;
		$this->_widget['params']['widgetTitle'] = new XenForo_Phrase($this->_widget['titlePhraseName']);
	}

}