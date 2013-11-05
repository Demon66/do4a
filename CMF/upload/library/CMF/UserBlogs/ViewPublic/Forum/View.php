<?php

class CMF_UserBlogs_ViewPublic_Forum_View extends XFCP_CMF_UserBlogs_ViewPublic_Forum_View
{
	public function renderHtml()
	{
		parent::renderHtml();
		if (!empty($this->_params['forum']['about']))
		{
			$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
			$this->_params['forum']['aboutHtml'] = new XenForo_BbCode_TextWrapper($this->_params['forum']['about'], $bbCodeParser, array('lightBox' => false));
		}
	}
}