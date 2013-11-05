<?php

/**
* Data writer for threads.
*
* @package XenForo_Discussion
*/
class Social_DataWriter_Discussion_Thread extends XFCP_Social_DataWriter_Discussion_Thread
{
	protected function _getFields()
	{
		$fields = parent::_getFields();
		$fields['xf_thread']['promotion_date'] = array('type' => self::TYPE_INT, 'default' => 0);

		return $fields;
	}

    protected function _discussionPreSave()
   	{
        parent::_discussionPreSave();
        if ($this->isChanged('first_post_likes') || $this->isChanged('node_id'))
        {
            $this->_promoteIfNeeded();
        }
   	}

  	public function rebuildDiscussionCounters($replyCount = false, $firstPostId = false, $lastPostId = false)
  	{
          XenForo_Application::getOptions()->set('socialMaxPromotionAge', 1000000000);
          $this->_promoteIfNeeded($this->get('post_date'));
          return parent::rebuildDiscussionCounters($replyCount, $firstPostId, $lastPostId);
  	}

    protected function _promoteIfNeeded($promotionDate = null)
   	{
        $promotionDate = $promotionDate ? $promotionDate : XenForo_Application::$time;
        $minLikes = XenForo_Application::getOptions()->get('socialBestNewsMinLikes');
        $maxPromotionAge = XenForo_Application::getOptions()->get('socialMaxPromotionAge');
        $threadAge = XenForo_Application::$time - $this->get('post_date');
        $forum = $this->_getForumData();
        if(!$this->get('promotion_date')  && $threadAge<= 3600*$maxPromotionAge && $this->get('first_post_likes') >= $minLikes && $forum['news_source']){
            $this->set('promotion_date', $promotionDate);
        }
   	}

}