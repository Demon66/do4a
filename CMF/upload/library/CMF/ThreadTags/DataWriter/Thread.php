<?php

/**
* Data writer for threads.
*
* @package XenForo_Discussion
*/
class CMF_ThreadTags_DataWriter_Thread extends XFCP_CMF_ThreadTags_DataWriter_Thread
{
	const DATA_TAGS = 'tagsInfo';

	/**
	 * Updates the search index for this discussion.
	 *
	 * @param array $messages List of messages in this discussion. Does not include text!
	 */
	protected function _indexForSearch()
	{
		if ($this->isChanged('tags') && $this->get('discussion_state') == 'visible' && $this->getExisting('discussion_state') == 'visible' && !$this->_needsSearchIndexUpdate())
		{
			$this->_insertIntoSearchIndex(array());
		}
		parent::_indexForSearch();
	}

	/**
	 * Rebuilds the counters of the discussion.
	 *
	 * @param integer|boolean $replyCount Total reply count, if known
	 * @param integer|boolean $firstPostId First post ID, if known already
	 * @param integer|boolean $lastPostId Last post ID, if known already
	 */
	public function rebuildDiscussionCounters($replyCount = false, $firstPostId = false, $lastPostId = false)
	{
		$this->set('tags', $this->_getTagModel()->getValidatedTagSet($this->get('tags')));
		parent::rebuildDiscussionCounters($replyCount, $firstPostId, $lastPostId);
	}


	/**
	 * Prepares tag set.
	 *
	 * @param string $tagSet
	 * @return boolean
	 */
	protected function _prepareTagSet(&$tagSet)
	{
		$tagArray = $this->_getTagModel()->prepareTagArray($tagSet, $this->getExisting('tags'));
		$tagSet = $tagArray['set'];
		$this->setExtraData(self::DATA_TAGS, $tagArray);
		return true;
	}

	protected function _save()
	{
		parent::_save();
		if ($this->isChanged('tags') && ($tagArray = $this->getExtraData(self::DATA_TAGS)))
		{
			$tagModel=$this->_getTagModel();
			if ($tagArray['add'])
			{
				$tagModel->addTags($tagArray['add']);
			}
			if ($tagArray['del'])
			{
				$tagModel->deleteTags($tagArray['del']);
			}
		}
	}

	/**
	 * @param array $messages
	 * Specific discussion post-delete behaviors.
	 */
	protected function _discussionPostDelete()
	{
		if ($tagSet = $this->getExisting('tags'))
		{
			$tagModel = $this->_getTagModel();
			$tagArray = $tagModel->prepareTagArray(array(), $tagSet);
			if ($tagArray['del'])
			{
				$tagModel->deleteTags($tagArray['del']);
			}
		}
		parent::_discussionPostDelete();
	}

	/**
	 * Gets the add-on model object.
	 *
	 * @return CMF_Core_Model_Tag
	 */
	protected function _getTagModel()
	{
		return $this->getModelFromCache('CMF_Core_Model_Tag');
	}

}