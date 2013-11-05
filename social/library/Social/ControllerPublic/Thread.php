<?php

/**
 * Controller for handling actions on threads.
 *
 * @package XenForo_Thread
 */

class Social_ControllerPublic_Thread extends XFCP_Social_ControllerPublic_Thread
{
	/**
	 * Inserts a new reply into an existing thread.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionAddReply()
	{
		$parentPostId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);

		$postWriterData = array('parent_post_id' => $parentPostId);
		XenForo_Application::set('postWriterData', $postWriterData);

		/* @var $response XenForo_ControllerResponse_View */
		return parent::actionAddReply();

	}

	public function getHelper($class)
	{
		if ($class == 'XenForo_ControllerHelper_ForumThreadPost' || $class == 'ForumThreadPost') {
			$class = 'Social_ControllerHelper_ForumThreadPost';
		}

		return parent::getHelper($class);
	}

    /**
     *
     * @return XenForo_ControllerResponse_Abstract
     */
    public function actionPromote()
    {
        $this->_assertPostOnly();

        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        /* @var $ftpHelper XenForo_ControllerHelper_ForumThreadPost*/
        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

        if (!$this->_getThreadModel()->canPromoteThread($thread, $forum, $errorPhraseKey))
        {
            throw $this->getErrorOrNoPermissionResponseException($errorPhraseKey);
        }

        $promotionDate = ($thread['promotion_date']>0) ? -1 : XenForo_Application::$time;
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $dw->setExistingData($threadId);
        $dw->set('promotion_date', $promotionDate);
        $dw->save();

        $modAction = ($promotionDate>0) ? 'promote' : 'unpromote';
        XenForo_Model_Log::logModeratorAction('thread', $thread, $modAction);

        $viewParams = array(
            'promotionDate' => $promotionDate,
        );

        return $this->responseView('Social_ViewPublic_Thread_PromotionConfirmed', '', $viewParams);

    }

    protected function _getDefaultViewParams(array $forum, array $thread, array $posts, $page = 1, array $viewParams = array())
   	{
        $viewParams = parent::_getDefaultViewParams($forum, $thread, $posts, $page, $viewParams);
   		$threadModel = $this->_getThreadModel();
        $viewParams['canPromoteThread'] = $threadModel->canPromoteThread($thread, $forum);

   		return $viewParams;
   	}

}