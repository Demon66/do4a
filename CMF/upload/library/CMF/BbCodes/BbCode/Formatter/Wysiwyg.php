<?php

class CMF_BbCodes_BbCode_Formatter_Wysiwyg extends XFCP_CMF_BbCodes_BbCode_Formatter_Wysiwyg
{
	public function getTags()
	{
		if ($this->_tags !== null)
		{
			return $this->_tags;
		}

		$this->_tags = parent::getTags();

		$this->_tags['md'] = array(
//				'trimLeadingLinesAfter' => 2,
			'hasOption' => false,
//				'stopLineBreakConversion' => true,
//				'stopSmilies' => true,
			'callback' => array($this, 'renderTagUndisplayable')
		);

		return $this->_tags;
	}

	public function renderTagUndisplayable(array $tag, array $rendererStates)
	{
		$output = parent::renderTagUndisplayable($tag, $rendererStates);
		switch ($tag['tag'])
		{
			case 'code':
			case 'php':
			case 'html':
			case 'md':
				return str_replace(array('> ', '  '), array('>&nbsp;', '&nbsp; '), $output);
			default:
				return $output;
		}
	}
}