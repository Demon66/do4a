<?php

class CMF_UserBlogs_Model_Node extends XFCP_CMF_UserBlogs_Model_Node
{
	/**
	 * Gets the bread crumb nodes for the specified node.
	 *
	 * @param array $node
	 * @param boolean $includeSelf If true, includes itself as the last entry
	 *
	 * @return array List of nodes that form bread crumbs, root down; [node id] => node info
	 */
	public function getNodeBreadCrumbs(array $node, $includeSelf = true)
	{
		$tail = array();
		//inside virtual node
		if (isset($node['blog_user_id']) && $node['node_id'] == XenForo_Application::get('options')->cmfUserBlogsNode)
		{
			if (!empty($node['userblogs_node_title'])) {
				$node['title']=$node['userblogs_node_title'];
			}
			$userBlogNode = $node;
			unset($node['blog_user_id'], $node['blog_username']);
			$tail = ($includeSelf) ?
				array(
					'user_blog_' . $node['node_id'] => array(
						'href' => XenForo_Link::buildPublicLink('full:forums', $userBlogNode),
						'value' => $userBlogNode['blog_username'],
						'node_id' => $userBlogNode['node_id']
					)
				)
				:
				array(
					$node['node_id'] => array(
						'href' => XenForo_Link::buildPublicLink('full:forums', $node),
						'value' => $node['title'],
						'node_id' => $node['node_id']
					)
				);
		}
		return parent::getNodeBreadCrumbs($node, $includeSelf) + $tail;
	}

}