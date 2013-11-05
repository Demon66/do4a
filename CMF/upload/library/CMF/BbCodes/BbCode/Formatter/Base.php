<?php
class CMF_BbCodes_BbCode_Formatter_Base extends XFCP_CMF_BbCodes_BbCode_Formatter_Base
{
	/** @var $_md CMF_BbCodes_BbCode_Formatter_MarkdownHelper */
	protected $_md = null;

	public function getTags()
	{
		if ($this->_tags !== null)
		{
			return $this->_tags;
		}
		$this->_tags = array_merge(parent::getTags(), array(
			'h2' => array(
				'trimLeadingLinesAfter' => 1,
				'optionRegex' => '/^[a-z0-9_ \-]+$/i',
				'plainChildren' => true,
				'stopSmilies' => true,
				'callback' => array($this, 'renderTagH')
			),
			'h3' => array(
				'trimLeadingLinesAfter' => 1,
				'optionRegex' => '/^[a-z0-9_ \-]+$/i',
				'plainChildren' => true,
				'stopSmilies' => true,
				'callback' => array($this, 'renderTagH')
			),
			'h4' => array(
				'trimLeadingLinesAfter' => 1,
				'optionRegex' => '/^[a-z0-9_ \-]+$/i',
				'plainChildren' => true,
				'stopSmilies' => true,
				'callback' => array($this, 'renderTagH')
			),
			'spoiler' => array(
				'trimLeadingLinesAfter' => 1,
				'callback' => array($this, 'renderTagSpoiler')
			),
			'md' => array(
				'trimLeadingLinesAfter' => 2,
				'hasOption' => false,
//				'stopLineBreakConversion' => true,
//				'stopSmilies' => true,
				'callback' => array($this, 'renderTagMarkdown')
			)

		));
		$this->_tags['attach']['plainChildren'] = false;
		$this->_tags['code']['trimLeadingLinesAfter'] = 0;
		$this->_tags['code']['stopLineBreakConversion'] = false;
		return $this->_tags;
	}

	public function renderTagH(array $tag, array $rendererStates)
	{
		if (($content = trim($this->stringifyTree($tag['children']))) === '')
		{
			return '';
		}
		$n = $tag['original'][0]{2};

		if ($tag['option'])
		{
			$name = trim($this->filterString($tag['option'], $rendererStates));

		}
		else
		{
			$name = '';
		}

		if ($this->_view)
		{
			$template = $this->_view->createTemplateObject('cmf_bb_code_tag_hx', array(
				'content' => $content,
				'nameHtml' => $name,
				'no' => $n
			));
			return $template->render();
		}
		else
		{
			return '<h' . $n . ' class="hx">' . (($name) ? '<a name="h_' . $name . '">' . $content . '</a>' : $content) . '</h' . $n . '>';
		}

	}

	public function parseValidateTagCode(array $tagInfo, $tagOption)
	{
		if (strtolower($tagOption) == 'markdown')
		{
			return true;
		}
		else
		{
			if (is_array($return = parent::parseValidateTagCode($tagInfo, $tagOption)))
			{
				$return['trimLeadingLinesAfter'] = 2;
				$return['stopLineBreakConversion'] = true;

			}
			else
			{
				$return = array('trimLeadingLinesAfter' => 2,
					'stopLineBreakConversion' => true);
			}
			return $return;
		}
	}

	public function renderTagCode(array $tag, array $rendererStates)
	{
		switch (strtolower(strval($tag['option'])))
		{
			case 'markdown':
				return $this->renderTagMarkdown($tag, $rendererStates);
		}

		return parent::renderTagCode($tag, $rendererStates);
	}

	public function renderTagMarkdown(array $tag, array $rendererStates)
	{
		if (!$this->_md)
		{
			$this->_md = new CMF_BbCodes_BbCode_Formatter_MarkdownHelper();
		}

//		$rendererStates['stopAutoLink']=true;
//		$rendererStates['markdown']=true;
		if (($content = trim($this->renderSubTree($tag['children'], $rendererStates))) === '')
		{
			return '';
		}
		if ($this->_view)
		{
			$template = $this->_view->createTemplateObject('');
			$template->addRequiredExternal('css', 'cmf_bb_code');
			$content = preg_replace(array('/\n[\n\t]+/', '#<br />\n#i'), array("\n", "\n"), $content);
//			$content = preg_replace(array('/\n[\n\t]+/','#<br />\n#i','/\n {8,}/'), array("\n","\n","\n<br />"), $content);
//			$content = str_ireplace(array('<br />'), array("\n"),$content);
//			$content = str_ireplace(array("\n",'<br />'), array("","\n"),$content);
			return '<div class="bbCodeMarkdown">' . $this->_md->transform($content) . '</div>';
		}

		return '<div class="bbCodeMarkdown">' . $content . '</div>';
	}

	public function renderTagSpoiler(array $tag, array $rendererStates)
	{
		if (($content = trim($this->renderSubTree($tag['children'], $rendererStates))) === '')
		{
			return '';
		}

		if ($tag['option'])
		{
			$name = trim($this->filterString($tag['option'], $rendererStates));

		}
		else
		{
			$name = '';
		}

		if ($this->_view)
		{
			$template = $this->_view->createTemplateObject('cmf_bb_code_tag_spoiler', array(
				'content' => $content,
				'nameHtml' => $name
			));
			return $template->render();
		}
		else
		{
			if ($name)
			{
				$name = '<div>' . $name . '</div>';
			}
			return '<div style="margin: 1em auto" title="Spoiler">' . $name . $content . '</div>';
		}

	}

	public function renderTagAttach(array $tag, array $rendererStates)
	{
//bug wtf???		$children_arr=explode(' ',trim($this->stringifyTree($tag['children']),2));
		$text = trim($this->renderSubTree($tag['children'], $rendererStates));
		$children_arr = explode(' ', $text, 2);
		$id = intval(array_shift($children_arr));
		if (!$id)
		{
			return '';
		}

		if (!$this->_view)
		{
			return '<a href="' . XenForo_Link::buildPublicLink('attachments', array('attachment_id' => $id)) . '">View attachment ' . $id . '</a>';
		}

		if (isset($rendererStates['attachments'][$id]) && is_array($rendererStates['attachments'][$id]))
		{
			$option_arr = explode(';', $tag['option']);
			$option_res = array();
			$tag['option'] = '';
			$filter = '';
			$banner = '';

			foreach ($option_arr as $option_str)
			{
				$bbmod = trim(strtolower($option_str));
				if ($bbmod == 'full')
				{
					$tag['option'] = 'full';
				}
				else if ($bbmod == 'ban')
				{
					$banner = true;
				}
				else if ($bbmod == 'show')
				{
				}
				else if (array_key_exists($bbmod, CMF_BbCodes_Listener::$validFilters) && !$filter)
				{
					$filter = $bbmod;
				}
				else
				{
					$option_res[] = $option_str;
				}
			}
			$option_str = implode(';', $option_res);
			$children_str = trim(implode(' ', $children_arr));
			if ($filter && $tag['option'] != 'full')
			{
				$rendererStates['attachments'][$id]['thumbnailUrl'] = XenForo_Link::buildPublicLink('attachments', array_merge($rendererStates['attachments'][$id], array('live_filter' => $filter)));
			}
			$title = ($children_str) ? $children_str : $option_str;
			$banner = ($banner || trim($title));

			$rendererStates['attachments'][$id]['imageWrapper'] = ($banner || !empty($rendererStates['thread'])); //may be more vars with ||
			$rendererStates['attachments'][$id]['imageTitle'] = str_ireplace("<br />\n", "<br />", $title);
			$rendererStates['attachments'][$id]['imageBanner'] = $banner;
			if (!empty($rendererStates['thread']))
			{
				$rendererStates['attachments'][$id]['thread'] = $rendererStates['thread'];
			}
		}
		return parent::renderTagAttach($tag, $rendererStates);
	}

	public function preLoadTemplates(XenForo_View $view)
	{
		parent::preLoadTemplates($view);
		$view->preLoadTemplate('cmf_bb_code_tag_spoiler');
		$view->preLoadTemplate('cmf_bb_code_tag_hx');
	}
}
