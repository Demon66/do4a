<?php

/**
 * Model for threads.
 *
 * @package XenForo_Thread
 */
class Social_Model_Thread extends XFCP_Social_Model_Thread
{

	public function prepareThreadConditions(array $conditions, array &$fetchOptions)
	{
		$fetchOptions['join'] = isset($fetchOptions['join']) ? $fetchOptions['join'] : 0x00;
		$sqlConditions[] = parent::prepareThreadConditions($conditions, $fetchOptions);
		$db = $this->_getDb();

		if (!empty($conditions['news_source'])) {
			$fetchOptions['join'] = $fetchOptions['join'] | XenForo_Model_Thread::FETCH_FORUM | XenForo_Model_Thread::FETCH_FORUM_OPTIONS;
			$sqlConditions[] = 'forum.news_source = 1';
		}

		if (!empty($conditions['max_post_date'])) {
			$sqlConditions[] = 'thread.post_date < ' . $db->quote($conditions['max_post_date']);
		}

        if (!empty($conditions['max_promotion_date'])) {
            $sqlConditions[] = 'thread.promotion_date < ' . $db->quote($conditions['max_promotion_date']);
        }

		if (!empty($conditions['promoted'])) {
			$sqlConditions[] = 'thread.promotion_date >= 0';
		}

		return $this->getConditionsForClause($sqlConditions);
	}

    public function prepareThreadFetchOptions(array $fetchOptions)
   	{
        $threadFetchOptions = parent::prepareThreadFetchOptions($fetchOptions);
        if(!empty($fetchOptions['order']) && $fetchOptions['order']=='promotion_date'){
            $threadFetchOptions['orderClause'] = 'ORDER BY thread.promotion_date DESC';
        }
        return $threadFetchOptions;
    }

    public function canPromoteThread(array $thread, array $forum, &$errorPhraseKey = '', array $nodePermissions = null, array $viewingUser = null)
   	{
   		$this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);
   		return ($viewingUser['user_id'] && XenForo_Permission::hasContentPermission($nodePermissions, 'socialPromoteThread'));
   	}
}