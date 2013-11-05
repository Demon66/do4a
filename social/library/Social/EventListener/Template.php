<?php


class Social_EventListener_Template
{

	public static function templateHook($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		$globalParams = $template->getParams();

		switch ($hookName)
		{
			case 'admin_forum_edit_tabs':
				$contents .= $template->create('social_admin_forum_edit_tab');
				break;

			case 'admin_forum_edit_panes':
				$contents .= $template->create('social_admin_forum_edit_pane', $globalParams);
				break;

			case 'ad_message_below':
				if (XenForo_Application::isRegistered('blogView') && $globalParams['commentsTemplate']) {
					$contents .= $template->create('social_comments_template', $globalParams);
				}
				break;

			case 'post_public_controls':
				if (XenForo_Application::isRegistered('blogView')) {
					$contents = $template->create('social_comment_public_controls', array_merge($globalParams, $hookParams));
				}
				break;

			case 'message_user_info_avatar':
				if (XenForo_Application::isRegistered('blogView') && !$hookParams['isQuickReply'] && !$hookParams['user']['isFirst']) {
					$contents = $template->create('social_message_user_info_avatar', $hookParams);
				}
				break;

            case 'thread_view_pagenav_before':
                if(XenForo_Application::getOptions()->get('socialNewsActive')){
                    $contents = $template->create('social_thread_promote', $globalParams);
                }
                break;

			case 'message_user_info_extra':
			case 'message_user_info_custom_fields':
			case 'message_user_info_text':
				if (XenForo_Application::isRegistered('blogView')) {
					$contents = '';
				}
				break;
		}


	}

	public static function templateCreate(&$templateName, array &$params, XenForo_Template_Abstract $template)
	{

		if ($templateName == 'PAGE_CONTAINER' && !empty(Social_Sidebar::$widgets)) {
            Social_Sidebar::prepareWidgets($params);
			$params['sidebar'] = Social_Sidebar::renderSidebar($template, $params);
		}

		switch ($templateName)
		{
			case 'PAGE_CONTAINER':
				if (XenForo_Application::get('options')->socialNewsIndex) {
					$params['showHomeLink'] = false;
				}
				break;

			case 'thread_view':
                if(XenForo_Application::getOptions()->get('socialNewsActive')){
                    $template->preloadTemplate('social_thread_promote');
                }
                if (XenForo_Application::isRegistered('blogView')) {
                    $template->preloadTemplate('social_comments_template');
                    $template->preloadTemplate('social_message_user_info_avatar');
                    $template->preloadTemplate('social_comment_public_controls');
                }
				break;
		}
	}

	public static function navigationTabs(array &$extraTabs, $selected)
	{
        if(XenForo_Application::getOptions()->get('socialNewsActive')){
            $extraTabs['news'] = array(
                'title' => (XenForo_Application::get('options')->socialNewsIndex) ? new XenForo_Phrase('social_home') : new XenForo_Phrase('social_news'),
                'href' => XenForo_Link::buildPublicLink('news'),
                'position' => 'home',
                'selected' => ($selected == 'news'),
                'linksTemplate' => false,
            );
        }

        if(XenForo_Application::getOptions()->get('socialBlogActive') && XenForo_Application::getOptions()->get('socialBlogRoot')){
            $extraTabs['blogs'] = array(
                'title' => new XenForo_Phrase('social_blogs'),
                'href' => XenForo_Link::buildPublicLink('blogs'),
                'linksTemplate' => (XenForo_Visitor::getUserId()) ? 'social_tab_links' : false,
                'position' => 'middle',
                'selected' => ($selected == 'blogs'),
            );
        }
	}
}