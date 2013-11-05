<?php

/**
 * Model class for manipulating the news feed.
 *
 * @author kier
 */
class Social_Model_NewsFeed extends XFCP_Social_Model_NewsFeed
{
	protected function _prepareNewsFeedItem(array $item, $handlerClassName, array $viewingUser)
	{
        if($handlerClassName == 'XenForo_NewsFeedHandler_DiscussionMessage_Post')
        {
            $handlerClassName = 'Social_NewsFeedHandler_DiscussionMessage_Post';
        }

        if($handlerClassName == 'XenForo_NewsFeedHandler_Discussion_Thread')
        {
            $handlerClassName = 'Social_NewsFeedHandler_Discussion_Thread';
        }

		return parent::_prepareNewsFeedItem($item, $handlerClassName, $viewingUser);
	}

}