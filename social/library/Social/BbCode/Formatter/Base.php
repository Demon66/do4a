<?php

/**
 * socialRefactored
 *
 * Base class for defining the formatting used by the BB code parser.
 * This class implements HTML formatting.
 *
 * @package XenForo_BbCode
 */
class Social_BbCode_Formatter_Base extends XFCP_Social_BbCode_Formatter_Base
{
	protected $_countMedia = 0;

	public function renderTree(array $tree, array $extraStates = array())
	{
		$this->_countMedia = 0;
		if (XenForo_Application::get('options')->socialAutoCenterMedia && XenForo_Application::isRegistered('blogView')) {
			$extraStates['autoCenter'] = true;
		}
		return parent::renderTree($tree, $extraStates);
	}

	public function getTags()
	{
		if ($this->_tags !== null) {
			return $this->_tags;
		}
		$this->_tags = array_merge(parent::getTags(), array(
			'cut' => array(
				'hasOption' => false,
				'replace' => array('', ''),
				'callback' => array($this, 'renderTagCut')
			)

		));
		return $this->_tags;
	}

	public function renderTagCut(array $tag, array $rendererStates)
	{
		if (empty($rendererStates['newsView'])) {
			$rendered = $this->renderSubTree($tag['children'], $rendererStates);
			return (strtolower($rendered) == 'end') ? '' : $rendered;
		}
		return '';
	}

	public function renderTagAlign(array $tag, array $rendererStates)
	{
		$rendererStates['autoCenter'] = false;
		return parent::renderTagAlign($tag, $rendererStates);
	}

	public function renderTagImage(array $tag, array $rendererStates)
	{
		$this->_countMedia++;
		if (!empty($rendererStates['maxMedia']) && $rendererStates['maxMedia'] < $this->_countMedia) return '';
		$rendered = parent::renderTagImage($tag, $rendererStates);
		return (!empty($rendererStates['autoCenter'])) ? '<div style="text-align: center">' . $rendered . '</div>' : $rendered;
	}

	public function renderTagMedia(array $tag, array $rendererStates)
	{
		$this->_countMedia++;
		if (!empty($rendererStates['maxMedia']) && $rendererStates['maxMedia'] < $this->_countMedia) return '';
		$rendered = parent::renderTagMedia($tag, $rendererStates);
		return (!empty($rendererStates['autoCenter'])) ? '<div style="text-align: center">' . $rendered . '</div>' : $rendered;
	}

	public function renderTagAttach(array $tag, array $rendererStates)
	{
		$this->_countMedia++;
		if (!empty($rendererStates['maxMedia']) && $rendererStates['maxMedia'] < $this->_countMedia) return '';
		$rendered = parent::renderTagAttach($tag, $rendererStates);
		return (!empty($rendererStates['autoCenter'])) ? '<div style="text-align: center">' . $rendered . '</div>' : $rendered;
	}
}