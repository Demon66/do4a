<?php

/**
 * Node handler for forum-type nodes.
 *
 * @package XenForo_Forum
 */
class CMF_UserBlogs_NodeHandler_UserBlog extends XenForo_NodeHandler_Abstract
{
	protected $_canView = true;
	protected $_parentNode = null;

	public function __construct(array $parentNode, array $parentPermissions)
	{
		if (!isset($parentNode['forum_read_date']))
		{
			$parentNode['forum_read_date'] = 0;
		}
		$this->_parentNode = $parentNode;
		$this->_canView = XenForo_Permission::hasContentPermission($parentPermissions, 'view');
	}

	/**
	 * Determines if the specified node is viewable with the given permissions.
	 *
	 * @param array $node Node info
	 * @param array $permissions Permissions for this node
	 *
	 * @return boolean
	 */
	public function isNodeViewable(array $node, array $permissions)
	{
		return $this->_canView;
	}

	/**
	 * Gets the effective data that can be pushed up to a parent node.
	 *
	 * @param array $node Current node info
	 * @param array $childPushable List of pushable data from all child nodes: [node id] => pushable data
	 * @param array $permissions Permissions for this node
	 *
	 * @return array List of pushable data (key-value pairs)
	 */
	public function getPushableDataForNode(array $node, array $childPushable, array $permissions)
	{
		if (!XenForo_Permission::hasContentPermission($permissions, 'viewOthers'))
		{
			return $this->_compileForumLikePushableData(array('privateInfo' => true), $childPushable);
		}

		return $this->_getForumLikePushableData($node, $childPushable);
	}

	/**
	 * Renders the specified node for display in a node tree.
	 *
	 * @param XenForo_View $view View object doing the rendering
	 * @param array $node Information about this node
	 * @param array $permissions Permissions for this node
	 * @param array $renderedChildren List of rendered children, [node id] => rendered output
	 * @param integer $level The level this node should be rendered at, relative to how it's to be displayed.
	 *
	 * @return string|XenForo_Template_Abstract
	 */
	public function renderNodeForTree(XenForo_View $view, array $node, array $permissions,
	                                  array $renderedChildren, $level
	)
	{
		$templateLevel = ($level <= 2 ? $level : 'n');

		return $view->createTemplateObject('node_forum_level_' . $templateLevel, array(
			'level' => $level,
			'forum' => $node,
			'renderedChildren' => $renderedChildren
		));
	}

	/**
	 * Do type-specific node preparations.
	 *
	 * @param array $node Unprepared data
	 *
	 * @return array Prepared data
	 */
	public function prepareNode(array $node)
	{
		$parentNode = $this->_parentNode;
		$node['forum_read_date'] = $parentNode['forum_read_date'];
		$node['hasNew'] = (empty($node['blog_has_new']) ? false : ($node['forum_read_date'] < $node['last_post_date']));
		$node['last_thread_title'] = $node['title'];
		$node['title'] = $node['username'];
		$node['message_count'] = $node['comments_count'];
		$node['userblogs_node_title'] = $parentNode['title'];
		$node['node_name'] = $parentNode['node_name'];
		if (!XenForo_Application::get('options')->cmfUserBlogsCounters)
		{
			$node['message_count'] += $node['discussion_count'];
		}
		return $node;
	}
}