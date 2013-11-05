<?php
class CMF_BbCodes_BbCode_Formatter_Text extends XFCP_CMF_BbCodes_BbCode_Formatter_Text
{
	public function getTags()
	{
		if ($this->_tags !== null)
		{
			return $this->_tags;
		}

		$this->_tags = array_merge(parent::getTags(), array(
			'h2' => array(
				'callback' => array($this, 'handleTag')
			),
			'h3' => array(
				'callback' => array($this, 'handleTag')
			),
			'h4' => array(
				'callback' => array($this, 'handleTag')
			),
			'spoiler' => array(
				'callback' => array($this, 'handleTag')
			),
			'md' => array(
				'callback' => array($this, 'renderTagMarkdown')
			)

		));

		return $this->_tags;
	}

	public function renderTagMarkdown(array $tag, array $rendererStates)
	{
		$return = $this->handleTag($tag, $rendererStates);
		return preg_replace(array(
//				'/(^|[\s])([#*_]+|[-=]{4,})/um',
//				'/[#*_]+($|[\s])/um'
				'/(^|[^\pL])([#_*]+|[-=]{4,})/um',
				'/[#*_]+($|[^\pL])/um'
			), '\1', $return
		);
	}
}
