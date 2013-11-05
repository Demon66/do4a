<?php

class Social_ControllerPublic_News extends XenForo_ControllerPublic_Abstract
{
	public function actionIndex()
	{
		$visitor = XenForo_Visitor::getInstance();
		$options = XenForo_Application::get('options');
		$action = $this->_routeMatch->getMinorSection();
		$oldestDate = $this->_input->filterSingle('oldest_date', XenForo_Input::DATE_TIME);
		$forumId = $this->_input->filterSingle('forum_id', XenForo_Input::UINT);
		$filter = $this->_input->filterSingle('filter', XenForo_Input::STRING);

		if ($action) {
			return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::RESOURCE_CANONICAL,
				XenForo_Link::buildBasicLink(($options->socialNewsIndex) ? 'index' : 'news', '')
			);
		}

		$fetchOptions = array(
			'join' => XenForo_Model_Thread::FETCH_USER | XenForo_Model_Thread::FETCH_AVATAR |
					XenForo_Model_Thread::FETCH_FIRSTPOST |XenForo_Model_Thread::FETCH_FORUM,
			'limit' => $options->socialNewsPerPage,
            'permissionCombinationId' => $visitor['permission_combination_id']
		);

        $conditions = array(
            'deleted' => false,
            'moderated' => false
        );

        switch($filter)
        {
            case 'latest':
                $fetchOptions['order']='post_date';
                $conditions['news_source'] = 1;
                $conditions['max_post_date'] = $oldestDate;
                $conditions['news_source'] = 1;
                break;
            default:
            case 'top':
                $fetchOptions['order']='promotion_date';
                $conditions['max_promotion_date'] = $oldestDate;
                $conditions['promoted'] = 1;
                break;
        }

		if ($forumId) $conditions['forum_id'] = $forumId;

		/* @var $threadModel XenForo_Model_Thread */
		$threadModel = $this->getModelFromCache('XenForo_Model_Thread');
		/* @var $forumModel XenForo_Model_Forum */
		$forumModel = $this->getModelFromCache('XenForo_Model_Forum');

		$newThreads = $threadModel->getThreads($conditions, $fetchOptions);
		
		foreach ($newThreads as $key => &$thread)
		{
            $thread['permissions'] = XenForo_Permission::unserializePermissions($thread['node_permission_cache']);

            if (!$threadModel->canViewThreadAndContainer($thread, $thread, $null, $thread['permissions']))
            {
                unset($newThreads[$key]);
                continue;
            }

			$thread = $threadModel->prepareThread($thread, $thread, $thread['permissions']);
			$thread['post_id'] = $thread['first_post_id'];
		}


		$postIds = array();
		foreach ($newThreads AS &$post)
		{
			$post['message']=Social_Helper_News::prepareNewsPost($post['message']);
			if (stripos($post['message'], '[/attach]')) $postIds[] = $post['post_id'];
			$oldestDate = $post[$fetchOptions['order']];
		}

		$newsNodes = $forumModel->getForums(array('news_source' => 1));
		if (empty($newsNodes[$forumId])) $forumId = 0;
		
		$viewParams = array(
			'title' => ($forumId) ? $newsNodes[$forumId]['title'] : $options->socialNewsTitle,
			'description' => ($forumId) ? $newsNodes[$forumId]['description'] : $options->socialNewsDescription,
			'oldestDate' => $oldestDate,
			'newsNodes' => $newsNodes,
			'threadsList' => array(),
			'newsList' => $newThreads,
			'forumId' => $forumId,
			'attachments' => (sizeof($postIds)) ? $this->getModelFromCache('XenForo_Model_Attachment')->prepareAttachments($this->getModelFromCache('XenForo_Model_Attachment')->getAttachmentsByContentIds('post', $postIds)) : array(),
		);

		Social_Widget_Abstract::create('Social_Widget_NewsNavigation', $viewParams, array('position' => -1000))->save();

		return $this->responseView('Social_ViewPublic_News_View', 'social_news', $viewParams);
	}


	public static function getSessionActivityDetailsForList(array $activities)
	{
		return new XenForo_Phrase('social_reading_news');
	}
}