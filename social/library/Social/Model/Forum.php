<?php

/**
 * Model for forums
 *
 * @package XenForo_Forum
 */
class Social_Model_Forum extends XFCP_Social_Model_Forum
{

	public function prepareForumConditions(array $conditions, array &$fetchOptions)
	{
		$sqlConditions[] = parent::prepareForumConditions($conditions, $fetchOptions);
		$db = $this->_getDb();

		if (!empty($conditions['news_source'])) {
			$sqlConditions[] = 'forum.news_source = 1 ';
		}

		return $this->getConditionsForClause($sqlConditions);
	}

}
