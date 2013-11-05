<?php

class Social_ViewPublic_News_View extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{
		$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

		$bbCodeOptions = array(
			'showSignature' => false,
			'noFollow' => true,
			'states' => array(
				'viewAttachments' => true,
				'attachments' => $this->_params['attachments'],
			)
		);
		Social_ViewPublic_Helper_News::bbCodeWrapMessages($this->_params['newsList'], $bbCodeParser, $bbCodeOptions);
	}

	public function renderJson()
	{
		$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));

		$bbCodeOptions = array(
			'showSignature' => false,
			'noFollow' => true,
			'states' => array(
				'viewAttachments' => true,
				'attachments' => $this->_params['attachments'],
			)
		);
		Social_ViewPublic_Helper_News::bbCodeWrapMessages($this->_params['newsList'], $bbCodeParser, $bbCodeOptions);

		$output = array('newsTemplateHtml' => array());

		foreach ($this->_params['newsList'] AS $thread)
		{
			$output['newsTemplateHtml']['#post-' . $thread['post_id']] =
					$this->createTemplateObject('social_news_item', array_merge($this->_params, array('thread' => $thread)))->render();
		}

		$output['title'] = $this->_params['title'];
		$output['description'] = $this->_params['description'];
		$output['oldestDate'] = $this->_params['oldestDate'];

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}