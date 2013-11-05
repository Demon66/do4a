<?php

/**
 * Handles searching of tags for all types.
 *
 * @package XenForo_Search
 */
class CMF_Core_Search_DataHandler_Tag extends XenForo_Search_DataHandler_Abstract
{
	/**
	 * @var CMF_Core_Model_Tag
	 */
	protected $_tagModel = null;

	public function processConstraint(XenForo_Search_SourceHandler_Abstract $sourceHandler, $constraint, $constraintInfo, array $constraints)
	{
		switch ($constraint)
		{
			case 'tag':
				if ($constraintInfo)
				{
					return array(
						'metadata' => array('tag', $constraintInfo),
					);
				}
		}

		return false;
	}

	/**
	 * Get the controller response for the form to search this type of content specifically.
	 *
	 * @param XenForo_ControllerPublic_Abstract $controller Invoking controller
	 * @param XenForo_Input $input Input object from controller
	 * @param array $viewParams View params prepared for general search
	 *
	 * @return XenForo_ControllerResponse_Abstract|boolean false
	 */
	public function getSearchFormControllerResponse(XenForo_ControllerPublic_Abstract $controller, XenForo_Input $input, array $viewParams)
	{
		$viewParams['tags'] = $this->_getTagModel()->getTagCloud();

		return $controller->responseView('CMF_Core_ViewPublic_Search_Form_Tag', 'cmf_search_form_tag', $viewParams);
	}

	protected function _insertIntoIndex(XenForo_Search_Indexer $indexer, array $data, array $parentData = null)
	{
	}

	protected function _updateIndex(XenForo_Search_Indexer $indexer, array $data, array $fieldUpdates)
	{
	}

	protected function _deleteFromIndex(XenForo_Search_Indexer $indexer, array $dataList)
	{
//		$indexer->deleteFromIndex('thread', $threadIds);
	}

	public function rebuildIndex(XenForo_Search_Indexer $indexer, $lastId, $batchSize)
	{
	}

	public function quickIndex(XenForo_Search_Indexer $indexer, array $contentIds)
	{
	}
	public function getDataForResults(array $ids, array $viewingUser, array $resultsGrouped)
	{
	}

	public function canViewResult(array $result, array $viewingUser)
	{
	}

	public function prepareResult(array $result, array $viewingUser)
	{
	}

	public function getResultDate(array $result)
	{
	}
	public function renderResult(XenForo_View $view, array $result, array $search)
	{
	}
	public function getSearchContentTypes()
	{
		return CMF_Core_Application::getCMF()->getTagContentTypes();
	}
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