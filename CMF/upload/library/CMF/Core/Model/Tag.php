<?php

/**
 * Model for tags.
 */
class CMF_Core_Model_Tag extends XenForo_Model
{
	const TAG_DELIMITER = " \n ";

	/**
	 * Get tag by name
	 *
	 * @param string $tagName
	 *
	 * @return mixed array|false
	 */
	public function getTagByName($tagName)
	{
		$tagName = $this->prepareTagName($tagName);

		return ($tagName) ? $this->_getDb()->fetchRow('
			SELECT *
			FROM cmf_tag
			WHERE tag = ?
		', $tagName) : false;
	}

	/**
	 * Get tag by id
	 *
	 * @param integer $tagId
	 *
	 * @return mixed array|false
	 */
	public function getTagById($tagId)
	{
		return ($tagId) ? $this->_getDb()->fetchRow('
			SELECT *
			FROM cmf_tag
			WHERE tag_id = ?
		', $tagId) : false;
	}

	/**
	 * Get tags by ids
	 *
	 * @param mixed|array $tagIds
	 *
	 * @return mixed array|false
	 */
	public function getTagsByIds($tagIds)
	{
		return ($tagIds) ? $this->fetchAllKeyed('
			SELECT *
			FROM cmf_tag
			WHERE tag_id IN (' . $this->_getDb()->quote($tagIds) . ')
		', 'tag_id') : false;
	}
	/**
	 * Get tags cloud
	 *
	 * @return mixed array|false
	 */
	public function getTagCloud()
	{
		$options = XenForo_Application::get('options');
		if (!$options->cmfTagsCloudLimit)
		{
			return array();
		}
		$tags = $this->fetchAllKeyed('
			SELECT *
			FROM (
				SELECT *
				FROM cmf_tag
				WHERE tag_count > 0
				ORDER BY tag_count DESC
				LIMIT ?
			) AS tagcloud
			ORDER BY tag ASC
		', 'tag_id', $options->cmfTagsCloudLimit);
		if ($tags)
		{
			$tags = $this->_prepareTagsForCloud($tags, $options->cmfTagsCloudLevels);
		}
		return $tags;
	}
	/**
	 * Get tags by exact names
	 *
	 * @param array|string $tagNames
	 * @param boolean $prepared
	 * @param boolean $checkAll
	 * @param boolean $autoCreate
	 *
	 * @return mixed array|false
	 */
	public function getTagsByNames($tagNames, $prepared = false, $checkAll = false, $autoCreate = false)
	{
		if (!is_array($tagNames) || !$prepared)
		{
			$tagNames = $this->getTagsArrayFromSet($tagNames);
		}
		if (!$tagNames)
		{
			return false;
		}

		$tags = $this->fetchAllKeyed('
				SELECT *
				FROM cmf_tag
				WHERE tag IN (' . $this->_getDb()->quote($tagNames) . ')
				', 'tag_id');

		$isNew = (sizeof($tagNames) != sizeof($tags));
		if ($checkAll && $isNew)
		{
			return false;
		}
		if ($autoCreate && $isNew)
		{
			$tagNamesDb = array();
			foreach ($tags as $tag)
			{
				$tagNamesDb[] = $tag['tag'];
			}
			$tagNamesAdd=array_diff($tagNames, $tagNamesDb);
			$this->addTags($tagNamesAdd);
			return $this->getTagsByNames($tagNames, true);
		}

		return $tags;
	}

	public function getTags(array $conditions = array(), $fetchOptions = array())
	{
		$whereClause = $this->prepareTagConditions($conditions, $fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->_getDb()->fetchAll($this->limitQueryResults('
			SELECT *
			FROM cmf_tag
			WHERE ' . $whereClause . '
			ORDER BY tag
		', $limitOptions['limit'], $limitOptions['offset']));
	}

	public function countTags(array $conditions = array(), $fetchOptions = array())
	{
		$whereClause = $this->prepareTagConditions($conditions, $fetchOptions);

		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM cmf_tag
			WHERE ' . $whereClause . '
		');
	}


	public function prepareTagName($tagName, $checkLength = true)
	{
		$tagName = XenForo_Helper_String::censorString(utf8_trim(utf8_strtolower($tagName)), null, '');
		if ($checkLength && (utf8_strlen($tagName) < XenForo_Application::get('options')->searchMinWordLength))
		{
			$tagName = '';
		}
		return $tagName;
	}

	public function getTagsArrayFromSet($tagSet)
	{
		$tagArray=(is_array($tagSet)) ? $tagSet : explode(',', strval($tagSet));
		$tagPrepared=array();
		foreach($tagArray as $tag) {
			$tag=$this->prepareTagName($tag);
			if ($tag!=='') {
				$tagPrepared[$tag]=true;
			}
		}
		return array_keys($tagPrepared);
	}

	public function prepareTagArray($tagSet='', $oldSet='')
	{
		$tagPrepared=$this->getTagsArrayFromSet($tagSet);
		$oldPrepared=$this->getTagsArrayFromSet($oldSet);

		return array(
			'set' => implode(', ', $tagPrepared),
			'new' => $tagPrepared,
			'old' => $oldPrepared,
			'add' => array_diff($tagPrepared, $oldPrepared),
			'del' => array_diff($oldPrepared, $tagPrepared)
		);
	}

	public function addTags($tags = null, $onlyCreate = false)
	{
		if ($tags)
		{
			if (!is_array($tags))
			{
				$tags=array($tags);
			}
			$db = $this->_getDb();
			$sqlValues = array();
			$increment = $onlyCreate ? '0' : '1';
			foreach ($tags as $tag)
			{
				$sqlValues[] = '(' . $db->quote($tag) . ', ' . $increment . ')';
			}
			$db->query('
				INSERT INTO cmf_tag
					(tag, tag_count)
				VALUES
					' . implode(', ', $sqlValues) . '
				ON DUPLICATE KEY UPDATE
					tag_count = tag_count + ' . $increment . '
			');
		}
	}
	public function deleteTags($tags = null, $hardDelete = false)
	{
		if ($tags)
		{
			if (!is_array($tags))
			{
				$tags = array($tags);
			}

			if ($hardDelete)
			{
				$this->_getDb()->query('
					DELETE FROM cmf_tag
					WHERE tag IN ('.$this->_getDb()->quote($tags).')
				');
			}
			else
			{
				$this->_getDb()->query('
					UPDATE cmf_tag SET
						tag_count = IF(tag_count > 1, tag_count - 1, 0)
					WHERE tag IN ('.$this->_getDb()->quote($tags).')
				');
			}
		}
	}
	/**
	 * Clear not exists tags from tagset
	 *
	 * @param string $tagSet tagSet string
	 *
	 * @return string
	 */
	public function getValidatedTagSet($tagSet = '')
	{
		$tagNames = $this->getTagsArrayFromSet($tagSet);
		if (!$tagNames)
		{
			return '';
		}
		$existingTags = $this->getTagsByNames($tagNames, true);
		if (!$existingTags)
		{
			return '';
		}

		if (sizeof($existingTags) != sizeof($tagNames))
		{
			$tagNames = array();
			foreach ($existingTags as $tag)
			{
				$tagNames[] = $tag['tag'];
			}
		}
		return implode(', ', $tagNames);
	}

	/**
	 * Prepares conditions for searching tags.
	 *
	 * @param array $conditions
	 * @param array $fetchOptions
	 *
	 * @return string SQL conditions
	 */
	public function prepareTagConditions(array $conditions, array &$fetchOptions)
	{
		$db = $this->_getDb();
		$sqlConditions = array();

		if (!empty($conditions['tag']))
		{
			if (is_array($conditions['tag']))
			{
				$sqlConditions[] = 'tag LIKE ' . XenForo_Db::quoteLike($conditions['tag'][0], $conditions['tag'][1], $db);
			}
			else
			{
				$sqlConditions[] = 'tag LIKE ' . XenForo_Db::quoteLike($conditions['tag'], 'lr', $db);
			}
		}
		if (!empty($conditions['tagArray']))
		{
			$sqlConditions[] = 'tag IN (' . $db->quote($conditions['tagArray']) . ')';
		}

		if (!empty($conditions['tagIds']))
		{
			$sqlConditions[] = 'tag_id IN (' . $db->quote($conditions['tagIds']) . ')';
		}

		return $this->getConditionsForClause($sqlConditions);
	}
	/**
	 * @param array $tags - Tags array
	 * @param integer $levels
	 *
	 * @return array
	 */
	protected function _prepareTagsForCloud(array $tags, $levels = 5)
	{
		if (!$tags)
		{
			return array();
		}
		if ($levels < 2)
		{
			foreach ($tags as &$tag)
			{
				$tag['level'] = 1;
			}
			return $tags;
		}
		$mean = 0;
		$n = 0;
		$M2 = 0;
		if (sizeof($tags)>1)
		{
			foreach($tags as $tag)
			{
				$x = $tag['tag_count'];
				$n++;
				$delta = $x - $mean;
				$mean += $delta/$n;
				$M2 += $delta*($x - $mean);
			}
			$stddev = sqrt($M2/($n-1));
		}
		else
		{
			$tag = reset($tags);
			$stddev = 0;
		}

		if ($stddev)
		{
			$low = 0;
			$high = 0;

			foreach ($tags as &$tag)
			{
				$tag['level'] = (($tag['tag_count'] - $mean) / $stddev);

				$low = min($tag['level'], $low);
				$high = max($tag['level'], $high);
			}

			foreach ($tags as &$tag)
			{
				$tag['level'] = round((($tag['level'] - $low) / ($high - $low)) * ($levels - 1)) + 1;
			}
		}
		else
		{
			foreach ($tags as &$tag)
			{
				$tag['level'] = round($levels / 2);
			}
		}

		return $tags;
	}

}