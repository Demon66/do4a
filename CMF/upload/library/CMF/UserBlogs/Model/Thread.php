<?php

/**
 * Model for threads.
 *
 * @package XenForo_Thread
 */
class CMF_UserBlogs_Model_Thread extends XFCP_CMF_UserBlogs_Model_Thread
{
	public function getThreadById($threadId, array $fetchOptions = array())
	{
		$thread = parent::getThreadById($threadId, $fetchOptions);
		if ($thread['node_id'] == XenForo_Application::get('options')->cmfUserBlogsNode)
		{
			//saving data for forum model
			XenForo_Application::set('userblogs', array(
				'node_id' => $thread['node_id'],
				'user_id' => $thread['user_id'],
				'username' => $thread['username']
			));
		}
		return $thread;
	}
}