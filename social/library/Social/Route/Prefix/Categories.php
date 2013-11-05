<?php

class Social_Route_Prefix_Categories extends XFCP_Social_Route_Prefix_Categories
{
	/**
	 * Method to build a link to the specified page/action with the provided
	 * data and params.
	 *
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{
		if ($data['node_id'] == XenForo_Application::get('options')->socialBlogRoot) {
			return XenForo_Link::buildBasicLink('blogs',$action,$extension);
//			return new XenForo_Link('blogs');
		}

		// for situations such as an array with thread and node info
		if (isset($data['node_title'])) {
			$data['title'] = $data['node_title'];
		}

		if ($data && isset($data['node_id']) && $data['depth'] === 0) {
			if (!XenForo_Application::get('options')->categoryOwnPage) {
				return new XenForo_Link(
					'forums/#' . XenForo_Link::buildIntegerAndTitleUrlComponent($data['node_id'], $data['title'])
				);
			}
		}
		return parent::buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, $extraParams);
	}
}