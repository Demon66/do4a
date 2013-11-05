<?php

/**
 * Handler for searching with MySQL's full text search.
 *
 * @package XenForo_Search
 */
class CMF_Core_Search_SourceHandler_MySqlFt extends XenForo_Search_SourceHandler_MySqlFt
{

	/**
	 * Inserts or replaces into the index.
	 *
	 * @see XenForo_Search_SourceHandler_Abstract::insertIntoIndex()
	 */
	public function insertIntoIndex($contentType, $contentId, $title, $message, $itemDate, $userId, $discussionId = 0, array $metadata = array())
	{
		$metadataPieces = array(
			$this->getMetadataKey('user', $userId),
			$this->getMetadataKey('content', $contentType)
		);
		foreach ($metadata AS $metadataKey => $value)
		{
			$metadataPiece = $this->getMetadataKey($metadataKey, $value);
			$metadataPieces[] = (is_array($metadataPiece)) ? implode(' ', $metadataPiece) : $metadataPiece;
		}

		$db = $this->_getDb();
		$row = '(' . $db->quote($contentType) . ', ' . $db->quote(intval($contentId))
			. ', ' . $db->quote($title) . ', ' . $db->quote($message)
			. ', ' . $db->quote(implode(' ', $metadataPieces))
			. ', ' . $db->quote(intval($itemDate)) . ', ' . $db->quote(intval($userId))
			. ', ' . $db->quote(intval($discussionId)) . ')';

		if ($this->_isRebuild)
		{
			$this->_bulkInserts[] = $row;
			$this->_bulkInsertLength += strlen($row);

			if ($this->_bulkInsertLength > 500000)
			{
				$this->_pushToIndex($this->_bulkInserts);

				$this->_bulkInserts = array();
				$this->_bulkInsertLength = 0;
			}
		}
		else
		{
			$this->_pushToIndex($row);
		}
	}

}