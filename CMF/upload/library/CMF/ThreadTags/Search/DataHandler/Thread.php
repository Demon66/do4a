<?php

/**
 * Handles searching of thread tags.
 *
 * @package XenForo_Search
 */
class CMF_ThreadTags_Search_DataHandler_Thread extends XFCP_CMF_ThreadTags_Search_DataHandler_Thread
{
	/**
	 * @var CMF_Core_Model_Tag
	 */
	protected $_tagModel = null;

	/**
	 * @return XenForo_DataWriter_Discussion_Thread
	 */
	public function getDataWriter()
	{
		$dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread', XenForo_DataWriter::ERROR_SILENT);
		$dw->setOption(XenForo_DataWriter_Discussion::OPTION_UPDATE_CONTAINER, false);

		return $dw;
	}

	//without parent::
	protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
	{
		$metadata = array();
		$metadata['node'] = $data['node_id'];
		$metadata['thread'] = $data['thread_id'];
		if (!empty($data['prefix_id']))
		{
			$metadata['prefix'] = $data['prefix_id'];
		}

		if (!empty($data['tags']) && ($tagsFull = $this->_getTagModel()->getTagsByNames($data['tags'], false, false, true)))
		{
			$tagNames = array();
			foreach ($tagsFull as $tag)
			{
				$tagNames[] = $tag['tag'];
			}
			$metadata['tag'] = array_keys($tagsFull);
			$data['tags'] = CMF_Core_Model_Tag::TAG_DELIMITER . implode(', ', $tagNames);
		}
		else
		{
			$data['tags'] = '';
		}
		$indexer->insertIntoIndex(
			'thread', $data['thread_id'],
			$data['title'], $data['tags'],
			$data['post_date'], $data['user_id'], $data['thread_id'], $metadata
		);
	}

	//_updateIndex not used anywhere

	/**
	 * @return CMF_Core_Model_Tag
	 */
	protected function _getTagModel()
	{
		if (!$this->_tagModel)
		{
			$this->_tagModel = XenForo_Model::create('CMF_Core_Model_Tag');
		}

		return $this->_tagModel;
	}

}