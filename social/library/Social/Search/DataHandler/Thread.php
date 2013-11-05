<?php

/**
 * Handles searching of threads.
 *
 * @package XenForo_Search
 */
class Social_Search_DataHandler_Thread extends XFCP_Social_Search_DataHandler_Thread
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
            return $view->createTemplateObject('search_result_blog_thread', array(
                'thread' => $result,
                'forum' => array(
                    'node_id' => $result['node_id'],
                    'title' => $result['node_title']
                ),
                'post' => $result,
                'search' => $search
            ));
        }

		return parent::renderResult($view, $result, $search);
	}

}