<?php

/**
 * Model for relations.
 */
class CMF_Core_Model_Relation extends XenForo_Model
{
	/**
	 * Gets relation by types and ids
	 *
	 * @param integer $contentId
	 * @param string $contentType
	 * @param integer $targetContentId
	 * @param string $targetContentType
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return mixed array|false
	 */
	public function getRelation($contentId, $contentType, $targetContentId, $targetContentType, array $fetchOptions = array())
	{
		$joinOptions = $this->prepareRelationJoinOptions($fetchOptions);
		return $this->_getDb()->fetchRow('
			SELECT cmf_relation.*
				' . $joinOptions['selectFields'] . '
			FROM cmf_relation AS relation
			' . $joinOptions['joinTables'] . '
			WHERE content_id = ?
				AND content_type = ?
				AND target_content_id = ?
				AND target_content_type = ?
		', $contentId, $contentType, $targetContentId, $targetContentType);
	}

	/**
	 * Gets relations that match the given conditions.
	 *
	 * @param array $conditions Conditions to apply to the fetching
	 * @param array $fetchOptions Collection of options that relate to fetching
	 *
	 * @return array Format: [thread id] => info
	 */
	public function getRelations(array $conditions, array $fetchOptions = array())
	{
		$whereConditions = $this->prepareRelationConditions($conditions, $fetchOptions);

		$joinOptions = $this->prepareRelationJoinOptions($fetchOptions);
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);

		return $this->fetchAllKeyed($this->limitQueryResults(
			'
				SELECT relation.*
					' . $joinOptions['selectFields'] . '
				FROM cmf_relation AS relation
				' . $joinOptions['joinTables'] . '
				WHERE ' . $whereConditions . '
				' . $joinOptions['orderClause'] . '
			', $limitOptions['limit'], $limitOptions['offset']
		), 'relation_id');
	}

	/**
	 * Prepares a collection of relations fetching related conditions into an SQL clause
	 *
	 * @param array $conditions List of conditions
	 * @param array $fetchOptions Modifiable set of fetch options (may have joins pushed on to it)
	 *
	 * @return string SQL clause (at least 1=1)
	 */
	public function prepareRelationConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions = array();
		$db = $this->_getDb();

		if (!empty($conditions['content_id']))
		{
			if (is_array($conditions['content_id']))
			{
				$sqlConditions[] = 'relation.content_id IN (' . $db->quote($conditions['content_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'relation.content_id = ' . $db->quote($conditions['content_id']);
			}
		}

		if (!empty($conditions['content_type']))
		{
			if (is_array($conditions['content_type']))
			{
				$sqlConditions[] = 'relation.content_type IN (' . $db->quote($conditions['content_type']) . ')';
			}
			else
			{
				$sqlConditions[] = 'relation.content_type = ' . $db->quote($conditions['content_type']);
			}
		}

		if (!empty($conditions['target_content_id']))
		{
			if (is_array($conditions['target_content_id']))
			{
				$sqlConditions[] = 'relation.target_content_id IN (' . $db->quote($conditions['target_content_id']) . ')';
			}
			else
			{
				$sqlConditions[] = 'relation.target_content_id = ' . $db->quote($conditions['target_content_id']);
			}
		}

		if (!empty($conditions['target_content_type']))
		{
			if (is_array($conditions['target_content_id']))
			{
				$sqlConditions[] = 'relation.target_content_type IN (' . $db->quote($conditions['target_content_type']) . ')';
			}
			else
			{
				$sqlConditions[] = 'relation.target_content_type = ' . $db->quote($conditions['target_content_type']);
			}
		}

		return $this->getConditionsForClause($sqlConditions);
	}

	/**
	 * Checks the 'join' key of the incoming array for the presence of the FETCH_x bitfields in this class
	 * and returns SQL snippets to join the specified tables if required
	 *
	 * @param array $fetchOptions containing a 'join' integer key build from this class's FETCH_x bitfields
	 *
	 * @return array Containing 'selectFields' and 'joinTables' keys. Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN foo ON (foo.id = other.id) '
	 */
	public function prepareRelationJoinOptions(array $fetchOptions)
	{
		return array(
			'selectFields' => '',
			'joinTables' => '',
			'orderClause' => ''
		);
	}
}