<?php

/**
* Data writer for relations
*
* @package Relations
*/
class CMF_Core_DataWriter_Relation extends XenForo_DataWriter
{
    /**
    * Gets the fields that are defined for the table. See parent for explanation.
    *
    * @return array
    */
    protected function _getFields()
    {
        return array(
            'cmf_relation' => array(
	            'content_id'   => array('type' => self::TYPE_UINT, 'required' => true),
	            'content_type' => array('type' => self::TYPE_STRING, 'required' => true, 'default' => 'thread'),
	            'target_content_id'   => array('type' => self::TYPE_UINT, 'required' => true),
	            'target_content_type' => array('type' => self::TYPE_STRING, 'required' => true, 'default' => 'thread'),
            )
        );
    }

    /**
    * Gets the actual existing data out of data that was passed in. See parent for explanation.
    *
    * @param mixed
    *
    * @return mixed array|false
    */
    protected function _getExistingData($data)
    {
	    if (!is_array($data))
	  		{
	  			return false;
	  		}
	  		else if (isset($data['content_id'], $data['content_type'], $data['target_content_id'], $data['target_content_type']))
	  		{
	  			$contentId = $data['content_id'];
	  			$contentType = $data['content_type'];
				$targetContentId = $data['target_content_id'];
	  			$targetContentType = $data['target_content_type'];
	  		}
	  		else if (isset($data[0], $data[1]))
	  		{
	  			$contentId = $data[0];
	  			$contentType = $data[1];
				$targetContentId = $data[2];
			  	$targetContentType = $data[3];
	  		}
	  		else
	  		{
	  			return false;
	  		}

        return array('cmf_relation' => $this->_getRelationModel()->getRelation($contentId, $contentType, $targetContentId, $targetContentType));
    }

    /**
    * Gets SQL condition to update the existing record.
    *
    * @param string $tableName Table name
    * @return string
    */
	protected function _getUpdateCondition($tableName)
	{
		return 'content_id = ' . $this->_db->quote($this->getExisting('content_id')) .
			' AND content_type = ' . $this->_db->quote($this->getExisting('content_type')) .
			' AND target_content_id = ' . $this->_db->quote($this->getExisting('target_content_id')) .
			' AND target_content_type = ' . $this->_db->quote($this->getExisting('target_content_type'));
	}
    
    /**
     * @return CMF_Core_Model_Relation
     */
    protected function _getRelationModel()
    {
        return $this->getModelFromCache('CMF_Core_Model_Relation');
    }
}