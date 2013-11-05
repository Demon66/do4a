<?php

class CMF_UserBlogs_Model_UserBlog extends XenForo_Model
{
	/**
	 * Gets the count of user blogs.
	 * @param array $conditions Conditions to apply to the fetching
	 *
	 * @return integer
	 */
	public function countUserBlogs(array $conditions)
	{
		if (!$forumId = XenForo_Application::get('options')->cmfUserBlogsNode)
		{
			return 0;
		}

		$conditions['forum_id'] = $forumId;

		$fetchOptions = array();
		$whereConditions = $this->prepareUserBlogConditions($conditions, $fetchOptions);
		//$sqlClauses = $this->prepareUserBlogFetchOptions($fetchOptions);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(DISTINCT user_id)
			FROM xf_thread AS thread
			' /* .  $sqlClauses['joinTables']
			without joins  */ . '
			WHERE ' . $whereConditions . '
		');
	}

	/**
	 * Gets the user blogs with the specified criteria.
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * node_id => user_id
	 * parent_node_id => userBlogNode
	 *
	 *
	 * @return array
	 */
	public function getUserBlogs(array $conditions, array $fetchOptions = array())
	{
		if (!$forumId = XenForo_Application::get('options')->cmfUserBlogsNode)
		{
			return array();
		}
		$conditions['forum_id'] = $forumId;
		$whereConditions = $this->prepareUserBlogConditions($conditions, $fetchOptions);


		$sqlClauses = $this->prepareUserBlogFetchOptions($fetchOptions);

		$fetchOptions['Inner'] = true;
		$sqlClausesInner = $this->prepareUserBlogFetchOptions($fetchOptions);

		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		/*
		  $forceIndex = (!empty($fetchOptions['forceThreadIndex']) ? 'FORCE INDEX (' . $fetchOptions['forceThreadIndex'] . ')' : '');

		  return $this->fetchAllKeyed($this->limitQueryResults('
			  SELECT thread.*,
				  thread.node_id AS parent_node_id,
				  \'UserBlog\' AS node_type_id,
				  CONCAT(\'user_blog_\', thread.user_id) AS node_id,
				  COUNT(*) AS discussion_count,
				  SUM(reply_count) AS comments_count
				  ' . $sqlClauses['selectFields'] . '
			  FROM xf_thread AS thread ' . $forceIndex . '
			  ' . $sqlClauses['joinTables'] . '
			  WHERE ' . $whereConditions . '
			  GROUP BY thread.user_id
			  ' . $sqlClauses['orderClause'] . '
			  ', $limitOptions['limit'], $limitOptions['offset']
		  ),'node_id');
  */
		return $this->fetchAllKeyed('
			SELECT
				stat.*,
				thread.*, thread.node_id as parent_node_id,
				CONCAT(\'user_blog_\', stat.user_id) AS node_id
				' . $sqlClauses['selectFields'] . '
			FROM (
				SELECT
					thread.user_id,
					thread.node_id AS parent_node_id,
					\'UserBlog\' AS node_type_id,
					COUNT(*) AS discussion_count,
					SUM(thread.reply_count) AS comments_count,
					MAX(thread.last_post_date) AS last_post_date
					' . $sqlClausesInner['selectFields'] . '
				FROM xf_thread as thread
				' . $sqlClausesInner['joinTables'] . '
				WHERE ' . $whereConditions . '
				GROUP BY thread.user_id
				' . $sqlClausesInner['orderClause'] . '
				LIMIT ' . $limitOptions['limit'] . ' OFFSET ' . $limitOptions['offset'] . '
			) AS stat

			LEFT JOIN xf_thread AS thread ON
				( stat.user_id = thread.user_id AND stat.parent_node_id = thread.node_id AND stat.last_post_date = thread.last_post_date)
			' . $sqlClauses['joinTables']
			, 'node_id');
	}

	/**
	 * Gets all the node data required for a node list display
	 * (eg, a forum list) from a given point. Returns 3 pieces of data:
	 *     * nodesGrouped - nodes, grouped by parent, with all data integrated
	 *  * nodeHandlers - list of node handlers by node type
	 *  * nodePermissions - the node permissions passed on
	 *
	 * @param array $parentNode Forum array
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 * @param array|null $parentNodePermissions Parent node permissions
	 *
	 * @return array Empty, or with keys: nodesGrouped, parentNodeId, nodeHandlers, nodePermissions
	 */
	public function getNodeUserDataForListDisplay(array $parentNode, array $conditions, array $fetchOptions = array(), array $parentNodePermissions = null)
	{
		$parentNodeId = XenForo_Application::get('options')->cmfUserBlogsNode;
		if (!$parentNodeId || $parentNode['node_id'] != $parentNodeId)
		{
			return array();
		}
		$userBlogs = $this->getUserBlogs($conditions, $fetchOptions);
		$nodeModel = $this->_getNodeModel();
		$nodePermissions = array();
		if (!is_array($parentNodePermissions))
		{
			$nodePermissions = $nodeModel->getNodePermissionsForPermissionCombination();
			$parentNodePermissions = (!empty($nodePermissions[$parentNodeId])) ? $nodePermissions[$parentNodeId] : array();
		}
		else
		{
			$nodePermissions[$parentNodeId] = $parentNodePermissions;
		}
		//permissions fix for virtual nodes
		$blogIds = array_keys($userBlogs);
		foreach ($blogIds as $blogId)
		{
			$nodePermissions[$blogId] = $parentNodePermissions;
		}


		$nodeHandlers = array(
			'UserBlog' => new CMF_UserBlogs_NodeHandler_UserBlog($parentNode, $parentNodePermissions)
		);

//		$nodes = $this->getViewableNodesFromNodeList($nodes, $nodeHandlers, $nodePermissions);
		$userBlogs = $nodeModel->mergeExtraNodeDataIntoNodeList($userBlogs, $nodeHandlers);

		//adding parent Node
		$userBlogs[$parentNodeId] = $parentNode;
		$nodeHandlers['Forum'] = new XenForo_NodeHandler_Forum();

		$userBlogs = $nodeModel->prepareNodesWithHandlers($userBlogs, $nodeHandlers);

		$groupedNodes = $nodeModel->groupNodesByParent($userBlogs);
		$groupedNodes = $nodeModel->pushNodeDataUpTree($parentNodeId, $groupedNodes, $nodeHandlers, $nodePermissions);
		if (
			$fetchOptions['page']==1 && !empty($fetchOptions['readUserId'])
			&& $fetchOptions['order'] == 'last_post_date' && $fetchOptions['orderDirection'] == 'desc'
			&& $parentNode['forum_read_date'] < $parentNode['last_post_date']
		) {
			$hasNew = false;
			foreach($groupedNodes[$parentNodeId] as $blogNode) {
				if (!empty($blogNode['hasNew'])) {
					$hasNew = true;
					break;
				}
			}
			if (!$hasNew) {
				$this->_getForumModel()->markForumReadIfNeeded($parentNode);
			}
		}

		return array(
			'nodesGrouped' => $groupedNodes,
			'parentNodeId' => $parentNodeId,
			'nodeHandlers' => $nodeHandlers,
			'nodePermissions' => $nodePermissions
		);
	}

	/*
		 $userBlogs = $userBlogModel->getUserBlogs($threadFetchConditions, $threadFetchOptions);
		 //TODO Refactor for pinned user blogs
		 $stickyBlogs = array();

		 // prepare all user blogs for blog list
		 foreach ($userBlogs AS &$blog)
		 {
			 $blog = $userBlogModel->prepareThread($blog, $forum, $permissions);
		 }
		 foreach ($stickyBlogs AS &$blog)
		 {
			 $blog = $userBlogModel->prepareThread($blog, $forum, $permissions);
		 }
		 unset($blog);

		 // if we've read everything on the first page of a normal sort order, probably need to mark as read
		 if ($visitor['user_id'] && $page == 1 && !$displayConditions
			 && $order == 'last_post_date' && $orderDirection == 'desc'
			 && $forum['forum_read_date'] < $forum['last_post_date']
		 )
		 {
			 $hasNew = false;
			 foreach ($threads AS $thread)
			 {
				 if ($thread['isNew'] && !$thread['isIgnored'])
				 {
					 $hasNew = true;
					 break;
				 }
			 }

			 if (!$hasNew)
			 {
				 // everything read, but forum not marked as read. Let's check.
				 $this->_getForumModel()->markForumReadIfNeeded($forum);
			 }
		 }
 */


	/**
	 * Prepares a collection of user blog fetching related conditions into an SQL clause
	 *
	 * @param array $conditions List of conditions
	 * @param array $fetchOptions Modifiable set of fetch options (may have joins pushed on to it)
	 *
	 * @return string SQL clause (at least 1=1)
	 */
	public function prepareUserBlogConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions = array();
		$db = $this->_getDb();

		if (!empty($conditions['forum_id']) && empty($conditions['node_id']))
		{
			$conditions['node_id'] = $conditions['forum_id'];
		}

		if (!empty($conditions['node_id']))
		{
			if (is_array($conditions['node_id']))
			{
				$sqlConditions[] = 'thread.node_id IN (' . $db->quote($conditions['node_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'thread.node_id = ' . $db->quote($conditions['node_id']);
			}
		}

		if (isset($conditions['deleted']) || isset($conditions['moderated']))
		{
			$sqlConditions[] = $this->prepareStateLimitFromConditions($conditions, 'thread', 'discussion_state');
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	/**
	 * Checks the 'join' key of the incoming array for the presence of the FETCH_x bitfields in this class
	 * and returns SQL snippets to join the specified tables if required
	 *
	 * @param array $fetchOptions containing a 'join' integer key build from this class's FETCH_x bitfields
	 *
	 * @return array Containing selectFields, joinTables, orderClause keys.
	 *         Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN foo ON (foo.id = other.id) '; orderClause = ORDER BY x.y
	 */
	public function prepareUserBlogFetchOptions(array $fetchOptions)
	{
		$selectFields = '';
		$joinTables = '';
		$orderBy = '';

		if (!empty($fetchOptions['order']))
		{
			switch ($fetchOptions['order'])
			{
				case 'last_post_date':
				default:
					$orderBy = 'last_post_date';
			}
			if (!isset($fetchOptions['orderDirection']) || $fetchOptions['orderDirection'] == 'desc')
			{
				$orderBy .= ' DESC';
			}
			else
			{
				$orderBy .= ' ASC';
			}
		}

		if (!empty($fetchOptions['join']))
		{
			if (($fetchOptions['join'] & XenForo_Model_Thread::FETCH_USER) && empty($fetchOptions['Inner']))
			{
				$selectFields .= ',
					user.*, IF(user.username IS NULL, thread.username, user.username) AS username';
				$joinTables .= '
					LEFT JOIN xf_user AS user ON
						(user.user_id = thread.user_id)';
			}
			/*
			   else if ($fetchOptions['join'] & self::FETCH_AVATAR)
			   {
				   $selectFields .= ',
					   user.avatar_date, user.gravatar';
				   $joinTables .= '
					   LEFT JOIN xf_user AS user ON
						   (user.user_id = thread.user_id)';
			   }
			   if ($fetchOptions['join'] & self::FETCH_FORUM)
			   {
				   $selectFields .= ',
					   node.title AS node_title';
				   $joinTables .= '
					   INNER JOIN xf_node AS node ON
						   (node.node_id = thread.node_id)';
			   }

			   if ($fetchOptions['join'] & self::FETCH_FORUM_OPTIONS)
			   {
				   $selectFields .= ',
					   forum.*';
				   $joinTables .= '
					   INNER JOIN xf_forum AS forum ON
						   (forum.node_id = thread.node_id)';
			   }

			   if ($fetchOptions['join'] & self::FETCH_FIRSTPOST)
			   {
				   $selectFields .= ',
					   post.message, post.attach_count';
				   $joinTables .= '
					   INNER JOIN xf_post AS post ON
						   (post.post_id = thread.first_post_id)';
			   }

			   if ($fetchOptions['join'] & self::FETCH_DELETION_LOG)
			   {
				   $selectFields .= ',
					   deletion_log.delete_date, deletion_log.delete_reason,
					   deletion_log.delete_user_id, deletion_log.delete_username';
				   $joinTables .= '
					   LEFT JOIN xf_deletion_log AS deletion_log ON
						   (deletion_log.content_type = \'thread\' AND deletion_log.content_id = thread.thread_id)';
			   }
   */
		}
		if (isset($fetchOptions['readUserId']) && !empty($fetchOptions['Inner']))
		{
			if (!empty($fetchOptions['readUserId']))
			{
				$autoReadDate = XenForo_Application::$time - (XenForo_Application::get('options')->readMarkingDataLifetime * 86400);

				$joinTables .= '
					LEFT JOIN xf_thread_read AS thread_read ON
						(thread_read.thread_id = thread.thread_id
						AND thread_read.user_id = ' . $this->_getDb()->quote($fetchOptions['readUserId']) . ')';

//				$joinForumRead = (!empty($fetchOptions['includeForumReadDate'])
//					|| (!empty($fetchOptions['join']) && $fetchOptions['join'] & self::FETCH_FORUM)
//				);
				if (false) //Not fetching forum_read data                //($joinForumRead)
				{
					$joinTables .= '
						LEFT JOIN xf_forum_read AS forum_read ON
							(forum_read.node_id = thread.node_id
							AND forum_read.user_id = ' . $this->_getDb()->quote($fetchOptions['readUserId']) . ')';

					$selectFields .= ",
						MIN(GREATEST(COALESCE(thread_read.thread_read_date, 0), COALESCE(forum_read.forum_read_date, 0), $autoReadDate)) AS blog_read_date";
				}
				else
				{
//					$selectFields .= ",
//						MIN(IF(thread_read.thread_read_date > $autoReadDate, thread_read.thread_read_date, $autoReadDate)) AS blog_read_date";
					$selectFields .= ",
						MAX(IF(GREATEST(COALESCE(thread_read.thread_read_date, 0), $autoReadDate) < thread.last_post_date, 1, 0)) AS blog_has_new";
				}
			}
			else
			{
				$selectFields .= ',
					1 AS blog_has_new';
			}
		}

		if (!empty($fetchOptions['permissionCombinationId']) && empty($fetchOptions['Inner']))
		{
			$selectFields .= ',
				permission.cache_value AS node_permission_cache';
			$joinTables .= '
				LEFT JOIN xf_permission_cache_content AS permission
					ON (permission.permission_combination_id = ' . $this->_getDb()->quote($fetchOptions['permissionCombinationId']) . '
						AND permission.content_type = \'node\'
						AND permission.content_id = thread.node_id)';
		}

		return array(
			'selectFields' => $selectFields,
			'joinTables' => $joinTables,
			'orderClause' => ($orderBy ? "ORDER BY $orderBy" : '')
		);
	}

	/**
	 * @return XenForo_Model_Node
	 */
	protected function _getNodeModel()
	{
		return $this->getModelFromCache('XenForo_Model_Node');
	}
	/**
	 * @return XenForo_Model_Forum
	 */
	protected function _getForumModel()
	{
		return $this->getModelFromCache('XenForo_Model_Forum');
	}
}