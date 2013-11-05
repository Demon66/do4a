<?php

class CMF_Core_ViewPublic_Search_SearchTag extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$results = array();
		foreach ($this->_params['tags'] AS $tag)
		{
			$results[$tag['tag']]['username'] = $tag['tag'];
		}

		return array(
			'results' => $results
		);
	}
}