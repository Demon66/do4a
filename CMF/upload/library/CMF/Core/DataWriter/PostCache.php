<?php

/**
 * Data writer for relations
 *
 * @package Relations
 */
class CMF_Core_DataWriter_PostCache extends XenForo_DataWriter
{
	/**
	 * Gets the fields that are defined for the table. See parent for explanation.
	 *
	 * @return array
	 */
	protected function _getFields()
	{
		return array(
			'cmf_post_cache' => array(
				'post_id' => array('type' => self::TYPE_UINT, 'required' => true),
				'post_cache' => array('type' => self::TYPE_STRING, 'required' => true,
					'requiredError' => 'please_enter_valid_message'
				),
				'cache_type' => array('type' => self::TYPE_STRING, 'default' => 'thread',
					'allowedValues' => array('thread', 'news')
				),
				'cache_date' => array('type' => self::TYPE_UINT, 'required' => true, 'default' => XenForo_Application::$time),
				'cache_filters' => array('type' => self::TYPE_STRING, 'required' => true)
			)
		);
	}

	/**
	 * Gets the actual existing data out of data that was passed in. See parent for explanation.
	 * @param mixed $data
	 * @return mixed array|false
	 */
	protected function _getExistingData($data)
	{
		if (!is_array($data))
		{
			return false;
		}
		else if (isset($data['post_id'], $data['cache_type']))
		{
			$postId = $data['post_id'];
			$cacheType = $data['cache_type'];
		}
		else if (isset($data[0], $data[1]))
		{
			$postId = $data[0];
			$cacheType = $data[1];
		}
		else
		{
			return false;
		}

		return array('cmf_post_cache' => $this->_getPostModel()->getPostCacheByTypeAndId($postId, $cacheType));
	}

	/**
	 * Gets SQL condition to update the existing record.
	 * @param string $tableName Table name
	 * @return string
	 */
	protected function _getUpdateCondition($tableName)
	{
		return 'post_id = ' . $this->_db->quote($this->getExisting('post_id')) .
			' AND cache_type = ' . $this->_db->quote($this->getExisting('cache_type'));
	}

	/**
	 * @return CMF_Core_Model_Post
	 */
	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}
}