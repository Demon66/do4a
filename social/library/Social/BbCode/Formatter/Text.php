<?php
class Social_BbCode_Formatter_Text extends XFCP_Social_BbCode_Formatter_Text
{
	public function getTags()
	{
		if ($this->_tags !== null) {
			return $this->_tags;
		}
		$this->_tags = array_merge(parent::getTags(), array(
			'cut' => array(
				'callback' => array($this, 'handleTag')
			),

		));

		return $this->_tags;
	}

}
