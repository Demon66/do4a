<?php

/**
 * Controller for handling actions on forums.
 *
 * @package XenForo_Forum
 */
class CMF_UserBlogs_ControllerPublic_Forum extends XFCP_CMF_UserBlogs_ControllerPublic_Forum
{
	/**
	 * Displays the contents of a forum.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionIndex()
	{
		$forumId = $this->_input->filterSingle('node_id', XenForo_Input::UINT);
		if (empty($forumId))
		{
			return parent::actionIndex();
		}
		
		
		$options = XenForo_Application::get('options');
		if (($forumId && $forumId != $options->cmfUserBlogsNode) || $this->_routeMatch->getResponseType() == 'rss' || $this->_input->filterSingle('user_id', XenForo_Input::UINT))
		{
			return parent::actionIndex();
		}
		
		/** @var $ftpHelper XenForo_ControllerHelper_ForumThreadPost */
		$ftpHelper = $this->getHelper('ForumThreadPost');
		$forum = $ftpHelper->assertForumValidAndViewable(
			$forumId,
			$this->_getForumFetchOptions()
		);
		$forumId = $forum['node_id'];

		$visitor = XenForo_Visitor::getInstance();
		$userBlogModel = $this->_getUserBlogModel();
		$forumModel = $this->_getForumModel();

		$page = max(1, $this->_input->filterSingle('page', XenForo_Input::UINT));
		$usersPerPage = $options->cmfUserBlogsPerPage;

		$this->canonicalizeRequestUrl(
			XenForo_Link::buildPublicLink('forums', $forum, array('page' => $page))
		);

		list($defaultOrder, $defaultOrderDirection) = $this->_getDefaultThreadSort($forum);

		// only default sort by last_post_date desc
		$order = $defaultOrder;
		$orderDirection = $defaultOrderDirection;

		//all threads and fake value for disabling "mark read"
		$displayConditions = array();

		$fetchElements = $this->_getThreadFetchElements(
			$forum, $displayConditions,
			$usersPerPage, $page, $order, $orderDirection
		);
		$threadFetchConditions = $fetchElements['conditions'];
		$threadFetchOptions = $fetchElements['options'] + array(
			'perPage' => $usersPerPage,
			'page' => $page,
			'order' => $order,
			'orderDirection' => $orderDirection
		);
		unset($fetchElements);


		$totalUserBlogs = $userBlogModel->countUserBlogs($threadFetchConditions);
		$this->canonicalizePageNumber($page, $usersPerPage, $totalUserBlogs, 'forums', $forum);

		$permissions = $visitor->getNodePermissions($forumId);

		// get the ordering params set for the header links
		$orderParams = array();
		$pageNavParams = $displayConditions;
//		$pageNavParams['order'] = ($order != $defaultOrder ? $order : false);
//		$pageNavParams['direction'] = ($orderDirection != $defaultOrderDirection ? $orderDirection : false);
		$pageNavParams['order'] = false;
		$pageNavParams['direction'] = false;

		$userBlogs = $userBlogModel->getNodeUserDataForListDisplay($forum, $threadFetchConditions, $threadFetchOptions, $permissions);
		$viewParams = array(
			'nodeList' => $userBlogs,
			'forum' => $forum,
			'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum, false),

			'canPostThread' => $forumModel->canPostThreadInForum($forum),
			'canSearch' => $visitor->canSearch(),

			//TODO Ignore List
			'ignoredNames' => array(), //$this->_getIgnoredContentUserNames($threads) + $this->_getIgnoredContentUserNames($stickyThreads),

			'order' => $order,
			'orderDirection' => $orderDirection,
			'orderParams' => $orderParams,
			'displayConditions' => $displayConditions,

			'pageNavParams' => $pageNavParams,
			'page' => $page,
			'blogsStartOffset' => ($page - 1) * $usersPerPage + 1,
			'blogsEndOffset' => ($page - 1) * $usersPerPage + count($userBlogs['nodesGrouped'][$forumId]),
			'blogsPerPage' => $usersPerPage,
			'totalBlogs' => $totalUserBlogs,

			'showPostedNotice' => $this->_input->filterSingle('posted', XenForo_Input::UINT)
		);

		return $this->responseView('XenForo_ViewPublic_Forum_View', 'cmf_user_blog_view', $viewParams);
	}

	protected function _getThreadFetchElements(array $forum, array $displayConditions)
	{
		if ($forum['node_id'] == XenForo_Application::get('options')->cmfUserBlogsNode && isset($forum['blog_user_id']))
		{
			$displayConditions['user_id'] = $forum['blog_user_id'];
		}
		return parent::_getThreadFetchElements($forum, $displayConditions);
	}

	protected function _getForumFetchOptions()
	{
		$return = parent::_getForumFetchOptions();
		return ($userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT)) ? ($return + array('blog_user_id' => $userId)) : $return;
	}

	/**
	 * @return CMF_UserBlogs_Model_UserBlog
	 */
	protected function _getUserBlogModel()
	{
		return $this->getModelFromCache('CMF_UserBlogs_Model_UserBlog');
	}


}