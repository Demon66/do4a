<?php

/**
 * View handling for viewing the details of a specific category.
 *
 * @package XenForo_Nodes
 */
class Social_ViewPublic_Category_View extends XFCP_Social_ViewPublic_Category_View
{
	/**
	 * Help render the HTML output.
	 *
	 * @return mixed
	 */
	public function renderHtml()
	{
		if (!empty($this->_params['category']['node_id']) && $this->_params['category']['node_id'] == XenForo_Application::get('options')->socialBlogRoot) {
			$this->_params['renderedNodes'] = XenForo_ViewPublic_Helper_Node::renderNodeTreeFromDisplayArray(
				$this, $this->_params['nodeList'], 2
			);
		}
		else
		{
			parent::renderHtml();
		}
	}
}