<?php

class CMF_Core_ViewAdmin_Tag_List extends XenForo_ViewAdmin_Base
{
	public function renderJson()
	{
		if (!empty($this->_params['filterView']))
		{
//			$this->_templateName = 'phrase_list_items';
			$this->_templateName = 'cmf_tag_list_items';
		}

		return null;
	}
}