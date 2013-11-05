<?php

/**
 * Helper for forum, thread, and post related pages.
 * Provides validation methods, amongst other things.
 *
 * @package XenForo_Thread
 */
class Social_ControllerHelper_ForumThreadPost extends XenForo_ControllerHelper_ForumThreadPost
{

	/**
	 * Checks that a forum is valid and viewable, before returning the forum's info.
	 *
	 * @param integer|string $forumIdOrName ID or node name of forum
	 * @param array $fetchOptions Extra data to fetch wtih the forum
	 *
	 * @return array Forum info
	 */
	public function assertForumValidAndViewable($forumIdOrName, array $fetchOptions = array())
	{
		$forum = parent::assertForumValidAndViewable($forumIdOrName, $fetchOptions);

		if (Social_Helper_Node::typeView('blog', $forum))
        {
			$visitor = XenForo_Visitor::getInstance();
			$visitor['content_show_signature'] = 0;
			XenForo_Application::set('blogView', 1);
			XenForo_Application::get('options')->messagesPerPage = 10000;
			$this->_controller->getRouteMatch()->setSections('blogs');
		}

		return $forum;
	}
}