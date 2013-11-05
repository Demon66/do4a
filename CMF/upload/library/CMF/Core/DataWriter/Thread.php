<?php

/**
 * Data writer for threads.
 *
 * @package XenForo_Discussion
 */
class CMF_Core_DataWriter_Thread extends XFCP_CMF_Core_DataWriter_Thread
{

	protected function _getFields()
	{
		$fields = parent::_getFields();
		$cmfFields = CMF_Core_Application::getCMF()->get('dw_fields', 'thread');
		$cmfFields['thread_id'] = array('type' => self::TYPE_UINT, 'default' => array('xf_thread', 'thread_id'), 'required' => true);
		if (sizeof($cmfFields)>1) {
			$fields['cmf_thread'] = $cmfFields;
		}
		return $fields;
	}

	protected function _discussionPreSave()
	{
		if ($threadFields=CMF_Core_Application::getCMF()->get('dw_data', 'thread'))
		{
			$this->bulkSet($threadFields);
		}
		parent::_discussionPreSave();
	}

	protected function _getExistingData($data)
	{
		if (!$threadId = $this->_getExistingPrimaryKey($data))
		{
			return false;
		}

		if (!$thread = $this->_getThreadModel()->getThreadById($threadId))
		{
			return false;
		}

		return $this->getTablesDataFromArray($thread);
	}

	protected function _update()
	{
		if (!empty($this->_existingData['cmf_thread']) && !empty($this->_newData['cmf_thread']) && sizeof($this->_existingData['cmf_thread'])==1)
		{
			//load defaults
			$this->_resolveDefaultsForCMFInsert('cmf_thread');
			if (isset($this->_newData['cmf_thread']['thread_id']) && $this->_newData['cmf_thread']['thread_id'])
			{
				$this->_db->insert('cmf_thread', $this->_newData['cmf_thread']);
			}
		}
		parent::_update();
	}

	protected function _delete()
	{
		$forceDelete = empty($this->_fields['cmf_thread']);

		if ($forceDelete)
		{
			//force delete cmf_thread rows
			$this->_fields['cmf_thread']['thread_id'] = array('type' => self::TYPE_UINT, 'default' => array('xf_thread', 'thread_id'), 'required' => true);
			$this->_existingData['cmf_thread']['thread_id'] = $this->_existingData['xf_thread']['thread_id'];
		}

		parent::_delete();

		if ($forceDelete)
		{
			//revert changes
			unset($this->_fields['cmf_thread'], $this->_existingData['cmf_thread']);
		}
	}

	protected function _resolveDefaultsForCMFInsert($tableName)
	{
		if (!empty($this->_fields[$tableName]))
		{
			foreach ($this->_fields[$tableName] AS $field => $fieldData)
			{
				// when default is an array it references another column in an earlier table
				if (!isset($this->_newData[$tableName][$field]) && isset($fieldData['default']))
				{
					if (!is_array($fieldData['default']))
					{
						$this->_newData[$tableName][$field] = $fieldData['default'];
					}
					else if (($value = $this->getExisting(array_pop($fieldData['default']), array_pop($fieldData['default'])))!==null)
					{
						$this->_newData[$tableName][$field] = $value;
					}
				}
			}
		}
	}
}