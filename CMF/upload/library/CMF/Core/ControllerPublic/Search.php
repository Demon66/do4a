<?php

class CMF_Core_ControllerPublic_Search extends XFCP_CMF_Core_ControllerPublic_Search
{
	public function actionSearchTags()
	{
		$q = $this->_getTagModel()->prepareTagName($this->_input->filterSingle('q', XenForo_Input::STRING), false);

		if ($q !== '')
		{
			$tags = $this->_getTagModel()->getTags(
				array('tag' => array($q , 'r')),
				array('limit' => 10)
			);
		}
		else
		{
			$tags = array();
		}

		$viewParams = array(
			'tags' => $tags
		);

		return $this->responseView(
			'CMF_Core_ViewPublic_Search_SearchTag',
			'',
			$viewParams
		);
	}
	protected function _handleInputType(array &$input = array())
	{
		$type = parent::_handleInputType($input);
		if ($type == 'tag')
		{
			$tagSet = $this->_getTagModel()->getTagsArrayFromSet($input['keywords']);
			$input['tagSet'] = $tagSet;
			$input['keywords'] = implode(', ', $tagSet);
		}
		return $type;
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