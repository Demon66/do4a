<?php

class CMF_Core_Model_Search extends XFCP_CMF_Core_Model_Search
{
	/**
	 * Gets the search data handler for a specific content type.
	 *
	 * @param string $contentType
	 *
	 * @return XenForo_Search_DataHandler_Abstract|boolean false
	 */
	public function getSearchDataHandler($contentType)
	{
		return ($contentType == 'tag' && XenForo_Application::get('options')->cmfTags && CMF_Core_Application::getCMF()->getTagContentTypes()) ? XenForo_Search_DataHandler_Abstract::create('CMF_Core_Search_DataHandler_Tag') : parent::getSearchDataHandler($contentType);
	}
	/**
	 * Gets the general search constraints from an array of input.
	 *
	 * @param array $input
	 * @param mixed $errors Returns a list of errors that occurred when getting constraints
	 *
	 * @return array Constraints
	 */
	public function getGeneralConstraintsFromInput(array $input, &$errors = null)
	{
		$constraints = parent::getGeneralConstraintsFromInput($input, $errors);

		if (!empty($input['tagSet']))
		{
			/* @var $tagModel CMF_Core_Model_Tag */
			$tagModel = $this->getModelFromCache('CMF_Core_Model_Tag');

			if ($tags = $tagModel->getTagsByNames($input['tagSet'], true, true))
			{
				$constraints['tag'] = array_keys($tags);
			}
			else
			{
				$errors[] = new XenForo_Phrase('cmf_tags_not_found');
			}
		}

		return $constraints;
	}

}