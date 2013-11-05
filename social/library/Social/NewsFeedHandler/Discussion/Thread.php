<?php

/**
 * Class to handle turning raw thread news feed events into renderable output
 *
 * @author kier
 *
 */
class Social_NewsFeedHandler_Discussion_Thread extends XenForo_NewsFeedHandler_Discussion_Thread
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