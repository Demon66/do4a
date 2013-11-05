<?php

class CMF_Core_ControllerAdmin_Tag extends XenForo_ControllerAdmin_Abstract
{
//	protected function _preDispatch($action)
//	{
//		$this->assertAdminPermission('tags');
//	}


	/**
	* Displays the list of phrases in the specified language.
	*
	* @return XenForo_ControllerResponse_Abstract
	*/
	public function actionIndex()
	{
		$tagModel = $this->_getTagModel();

		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = 100;

		$conditions = array();

		$filter = $this->_input->filterSingle('_filter', XenForo_Input::ARRAY_SIMPLE);
		if ($filter && isset($filter['value']))
		{
			$conditions['tag'] = array($filter['value'], empty($filter['prefix']) ? 'lr' : 'r');
			$filterView = true;
		}
		else
		{
			$filterView = false;
		}

		$fetchOptions = array(
			'page' => $page,
			'perPage' => $perPage
		);

		$totalTags = $tagModel->countTags($conditions);

		$viewParams = array(
			'tags' => $tagModel->getTags($conditions, $fetchOptions),

			'page' => $page,
			'perPage' => $perPage,
			'totalTags' => $totalTags,

			'filterView' => $filterView,
			'filterMore' => ($filterView && $totalTags > $perPage)
		);

		return $this->responseView('CMF_Core_ViewAdmin_Tag_List', 'cmf_tag_list', $viewParams);
	}

	/**
	 * Helper to get the tag add/edit form controller response.
	 *
	 * @param array $tag
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	protected function _getTagAddEditResponse(array $tag)
	{
		$viewParams = array(
			'tag' => $tag,
			'listItemId' => (!empty($tag['tag_id']) ? $tag['tag_id'] : 0),
		);

		return $this->responseView('CMF_Core_ViewAdmin_Tag_Edit', 'cmf_tag_edit', $viewParams);
	}


	/**
	 * Form to add a tag.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionAdd()
	{
		$tag = array(
			'tag_id' => 0
		);

		return $this->_getTagAddEditResponse($tag);
	}
	/**
	 * Form to edit a specified phrase. A language_id input must be specified. If the language ID
	 * of the requested phrase and the language ID of the input differ, the request is
	 * treated as adding a customized version of the requested phrase in the input
	 * language.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionEdit()
	{
		$input = $this->_input->filter(array(
			'tag_id' => XenForo_Input::UINT
		));

		$tag = $this->_getTagOrError($input['tag_id']);

		return $this->_getTagAddEditResponse($tag);
	}

	public function actionSave()
	{
		$this->_assertPostOnly();

		if ($this->_input->filterSingle('delete', XenForo_Input::STRING))
		{
			// user clicked delete
			return $this->responseReroute( __CLASS__ , 'delete');
		}

		$input = $this->_input->filter(array(
			'tag' => XenForo_Input::STRING,
			'tag_id' => XenForo_Input::UINT
		));
		$reload = $this->_input->filterSingle('reload', XenForo_Input::STRING);
		$tagModel = $this->_getTagModel();
		$tagId = $input['tag_id'];
		$tagName = $tagModel->prepareTagName($input['tag']);

		if (!$tagName)
		{
			return $this->responseError(new XenForo_Phrase('cmf_please_enter_valid_tag_name'));
		}

		$newTag = $tagModel->getTagByName($tagName);

		if (!$tagId) //create new tag
		{
			if ($newTag)
			{
				return $this->responseError(new XenForo_Phrase('cmf_tag_names_must_be_unique'));
			}

			$tagModel->addTags(array($tagName), true);

			$tag = $this->_getTagByNameOrError($tagName);

			return ($reload) ?
				$this->responseRedirect(
					XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED,
					XenForo_Link::buildAdminLink('tags/edit', $tag)
				) :
				$this->responseRedirect(
					XenForo_ControllerResponse_Redirect::SUCCESS,
					XenForo_Link::buildAdminLink('tags') . $this->getLastHash($tag['tag_id'])
				);
		}
		else
		{
			$tag = $this->_getTagOrError($tagId);
			if ($tagName == $tag['tag'])
			{
				return ($reload) ?
					$this->responseRedirect(
						XenForo_ControllerResponse_Redirect::RESOURCE_UPDATED,
						XenForo_Link::buildAdminLink('tags/edit', $tag)
					) :
					$this->responseRedirect(
						XenForo_ControllerResponse_Redirect::SUCCESS,
						XenForo_Link::buildAdminLink('tags') . $this->getLastHash($tag['tag_id'])
					);
			}

			if ($newTag)
			{
				if (!$this->isConfirmedPost())
				{
					$viewParams = array(
						'tag' => $tag,
						'new_tag' => $newTag,
						'reload' => $this->_input->filterSingle('reload', XenForo_Input::STRING)
					);

					return $this->responseView('CMF_Core_ViewAdmin_Tag_Merge', 'cmf_tag_merge', $viewParams);
				}
			}
			else
			{
				//create new tag and reassign content between old and new tag
				$tagModel->addTags(array($tagName), true);
				$newTag = $this->_getTagByNameOrError($tagName);
			}


			$options = array(
				'tag' => $tag['tag'],
				'new_tag' => $newTag['tag'],
			);
			return XenForo_CacheRebuilder_Abstract::getRebuilderResponse(
				$this, array(array('Tag', $options)),
				($reload) ?
					XenForo_Link::buildAdminLink('tags/edit', $newTag) . $this->getLastHash($newTag['tag_id']) :
					XenForo_Link::buildAdminLink('tags') . $this->getLastHash($newTag['tag_id'])
			);

		}
	}

	/**
	 * Deletes a language.
	 *
	 * @return XenForo_ControllerResponse_Abstract
	 */
	public function actionDelete()
	{
		$tagId = $this->_input->filterSingle('tag_id', XenForo_Input::UINT);
		$tag = $this->_getTagOrError($tagId);

		if ($this->isConfirmedPost())
		{
			$options = array(
				'tag' => $tag['tag'],
				'new_tag' => ''
			);

			return XenForo_CacheRebuilder_Abstract::getRebuilderResponse(
				$this, array(array('Tag', $options)),
					XenForo_Link::buildAdminLink('tags')
			);
		}
		else
		{
			$viewParams = array(
				'tag' => $tag
			);

			return $this->responseView('CMF_Core_ViewAdmin_Tag_Delete', 'cmf_tag_delete', $viewParams);
		}
	}

	/**
	 * Gets the specified tag or throws an error.
	 *
	 * @param integer $tagId
	 *
	 * @return array
	 */
	protected function _getTagOrError($tagId)
	{
		$tag = $this->_getTagModel()->getTagById($tagId);
		if (!$tag)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('cmf_requested_tag_not_found'), 404));
		}

		return $tag;
	}

	/**
	 * Gets the specified tag by name or throws an error.
	 *
	 * @param string $tagName
	 *
	 * @return array
	 */
	protected function _getTagByNameOrError($tagName)
	{
		$tag = $this->_getTagModel()->getTagByName($tagName);
		if (!$tag)
		{
			throw $this->responseException($this->responseError(new XenForo_Phrase('cmf_requested_tag_not_found'), 404));
		}

		return $tag;
	}

	/**
	 * Lazy load the phrase model object.
	 *
	 * @return  CMF_Core_Model_Tag
	 */
	protected function _getTagModel()
	{
		return $this->getModelFromCache('CMF_Core_Model_Tag');
	}

}