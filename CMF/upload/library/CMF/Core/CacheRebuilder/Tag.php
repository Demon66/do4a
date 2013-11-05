<?php

/**
 * Cache rebuilder for the search index.
 *
 * @package XenForo_CacheRebuild
 */
class CMF_Core_CacheRebuilder_Tag extends XenForo_CacheRebuilder_Abstract
{
	/**
	 * Gets rebuild message.
	 */
	public function getRebuildMessage()
	{
		return new XenForo_Phrase('cmf_tag');
	}

	/**
	 * Shows the exit link.
	 */
	public function showExitLink()
	{
		return true;
	}

	/**
	 * Rebuilds the data.
	 *
	 * @see XenForo_CacheRebuilder_Abstract::rebuild()
	 */
	public function rebuild($position = 0, array &$options = array(), &$detailedMessage = '')
	{
		$options = array_merge(array(
			'maxExecution' => XenForo_Application::getConfig()->rebuildMaxExecution
		), $options);

		$inputHandler = new XenForo_Input($options);
		$input = $inputHandler->filter(array(
			'tag' => XenForo_Input::STRING,
			'new_tag' =>  XenForo_Input::STRING,
			'delay' => XenForo_Input::UNUM
		));
		/* @var $tagModel CMF_Core_Model_Tag */
		$tagModel = XenForo_Model::create('CMF_Core_Model_Tag');

		$input['tag'] = $tagModel->prepareTagName($input['tag']);
		if (!$input['tag'] || !($tag = $tagModel->getTagByName($input['tag'])))
		{
			//nothing to do
			return true;
		}
		$tagId = $tag['tag_id'];
		$input['new_tag'] = $tagModel->prepareTagName($input['new_tag']);

		if ($input['delay'] >= 0.01)
		{
			usleep($input['delay'] * 1000000);
		}

		/* @var $searchModel XenForo_Model_Search */
		$searchModel = XenForo_Model::create('XenForo_Model_Search');
		if (!$tagHandler = $searchModel->getSearchDataHandler('tag'))
		{
			return true;
		}

		$searcher = new XenForo_Search_Searcher($searchModel);

		$results = $searcher->searchType(
			$tagHandler, $tag['tag'], array('tag' => $tag['tag_id']), 'date'
		);
		$handlers = $searchModel->getSearchDataHandlers($tagHandler->getSearchContentTypes());

		if (!$results || !$handlers)
		{
			//all done. delete old tag from DB
			$tagModel->deleteTags(array($tag['tag']), true);
			return true;
		}

		//for force delete invalid index data
		$indexer = new XenForo_Search_Indexer();

		$s = microtime(true);
		$byTimeout = false;
		$count = 0;

		foreach ($results as $result)
		{

			list($type, $id) = $result;
			if (empty($handlers[$type]) && !$id)
			{
				continue;
			}

			$typeHandler = $handlers[$type];
			if (!method_exists($typeHandler, 'getDataWriter'))
			{
				continue;
			}
			/** @var $dw XenForo_DataWriter */
			$dw = $typeHandler->getDataWriter();

			if ($dw->setExistingData($id))
			{
				//
				$tagArray = $tagModel->getTagsArrayFromSet($dw->get('tags'));
				$tagArray = array_diff($tagArray, array($tag['tag']));
				if ($input['new_tag'] && !in_array($input['new_tag'], $tagArray))
				{
					$tagArray[]=$input['new_tag'];
				}

				$dw->set('tags', implode(', ', $tagArray));
				$dw->save();
			}
			else
			{
				//removing from search index
				$indexer->deleteFromIndex($type, $id);
			}
			$count++;

			$maxExec = $options['maxExecution'] - ($s - microtime(true));
			if ($maxExec < 1)
			{
				$byTimeout = true;
				break;
			}
		}

		if (!$count && !$byTimeout)
		{
			//all done. delete old tag from DB
			$tagModel->deleteTags(array($tag['tag']), true);
			return true;
		}

		$options = array(
			'tag' => $input['tag'],
			'new_tag' => $input['new_tag'],
			'delay' => $input['delay']
		);

		$position += ($count) ? $count : 1;
		$detailedMessage = XenForo_Locale::numberFormat($position);

		return $position;
	}
}