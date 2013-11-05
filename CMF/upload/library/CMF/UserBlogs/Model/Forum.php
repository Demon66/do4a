<?php

/**
 * Model for forums
 *
 * @package XenForo_Forum
 */
class CMF_UserBlogs_Model_Forum extends XFCP_CMF_UserBlogs_Model_Forum
{
	/**
	 * Fetches the combined node-forum record for the specified node id
	 *
	 * @param integer $id Node ID
	 * @param array $fetchOptions Options that affect what is fetched
	 *
	 * @return array
	 */
	public function getForumById($id, array $fetchOptions = array())
	{
		$forum = parent::getForumById($id, $fetchOptions);
		if ($forum)
		{
			$user = false;
			if (!empty($fetchOptions['blog_user_id']))
			{
				$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($fetchOptions['blog_user_id'], array('join' => XenForo_Model_User::FETCH_USER_PROFILE));
			}
			else if (XenForo_Application::isRegistered('userblogs')) //data from thread model
			{
				$userSaved = XenForo_Application::get('userblogs');
				if ($userSaved['node_id'] == $id)
				{
					$user = $userSaved;
				}
			}
			if ($user)
			{
				$forum['userblogs_node_title'] = $forum['title'];
				//$forum['title'] = $user['username'];
				$forum['blog_user_id'] = $user['user_id'];
				$forum['blog_username'] = $user['username'];
				$forum['user'] = $user;
				$forum['about'] = !empty($user['about']) ? $user['about'] : '';
				$forum['description'] = '';
			}
		}
		return $forum;
	}

	/**
	 * Fetches the combined node-forum record for the specified node name
	 *
	 * @param string $name Node name
	 * @param array $fetchOptions Options that affect what is fetched
	 *
	 * @return array
	 */
	public function getForumByNodeName($name, array $fetchOptions = array())
	{
		$forum = parent::getForumByNodeName($name, $fetchOptions);
		if ($forum)
		{
			$user = false;
			if (!empty($fetchOptions['blog_user_id']))
			{
				$user = $this->getModelFromCache('XenForo_Model_User')->getUserById($fetchOptions['blog_user_id'], array('join' => XenForo_Model_User::FETCH_USER_PROFILE));
			}
			if ($user)
			{
				$forum['userblogs_node_title'] = $forum['title'];
				//$forum['title'] = $user['username'];
				$forum['blog_user_id'] = $user['user_id'];
				$forum['blog_username'] = $user['username'];
				$forum['user'] = $user;
				$forum['about'] = !empty($user['about']) ? $user['about'] : '';
				$forum['description'] = '';
			}
		}
		return $forum;
	}

	public function markForumReadIfNeeded(array $forum, array $viewingUser = null)
	{
		if (!empty($forum['blog_user_id']))
		{
			return false;
		}
		return parent::markForumReadIfNeeded($forum, $viewingUser);
	}
}
