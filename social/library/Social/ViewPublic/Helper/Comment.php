<?php

/**
 * Class to help display a node list/tree.
 *
 * @package XenForo_Comment
 */
class Social_ViewPublic_Helper_Comment
{
	public static function createCommentsTemplateObject(XenForo_View $view, &$comments)
	{

		if (empty($comments)) {
			return new XenForo_Template_Public('');
		}

		foreach ($comments as &$comment) {
			if (!empty($comment['children'])) {
				$comment['children']['template'] = self::createCommentsTemplateObject($view, $comment['children']);
			}
		}

		$viewParams = $view->getParams();
		$viewParams['posts'] = &$comments;

		return $view->createTemplateObject('social_comments', $viewParams);
	}


	public static function buildCommentTree(array &$comments)
	{

		$tree = array();

		foreach ($comments as $id => &$comment) {
			if (!$comment['parent_post_id']) {
				$tree[$id] = &$comment;
			}
			else if (isset($comments[$comment['parent_post_id']])) {
				$comments[$comment['parent_post_id']]['children'][$id] = &$comment;
			}
		}

		return $tree;
	}
}
 
