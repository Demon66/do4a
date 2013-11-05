<?php

/**
 * Class to handle turning raw post news feed events into renderable output
 *
 */
class Social_NewsFeedHandler_DiscussionMessage_Post extends XenForo_NewsFeedHandler_DiscussionMessage_Post
{
    protected function _prepareNewsFeedItemAfterAction(array $item, $content, array $viewingUser)
   	{
        if(Social_Helper_Node::typeView('blog',$content))
        {
           $item['content_type'] = 'blog_'.$item['content_type'];
        }

        return parent::_prepareNewsFeedItemAfterAction($item, $content, $viewingUser);
   	}
}