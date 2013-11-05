<?php

/**
 * Handles searching of posts.
 *
 * @package XenForo_Search
 */
class Social_Search_DataHandler_Post extends XFCP_Social_Search_DataHandler_Post
{


	/**
	 * Renders a result to HTML.
	 *
	 * @see XenForo_Search_DataHandler_Abstract::renderResult()
	 */
	public function renderResult(XenForo_View $view, array $result, array $search)
	{
        if(Social_Helper_Node::typeView('blog',$result))
        {
            return $view->createTemplateObject('search_result_blog_post', array(
                'post' => $result,
                'thread' => $result,
                'forum' => array(
                    'node_id' => $result['node_id'],
                    'title' => $result['node_title']
                ),
                'search' => $search
            ));
        }

        return parent::renderResult($view, $result, $search);
    }

}