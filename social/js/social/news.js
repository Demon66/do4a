/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
    XenForo.PromotionLink = function($link)
    {
        var $countContainer = $('.SelectionCountContainer');

        if ($countContainer.length)
        {
            $link
                .insertAfter($countContainer.children().first())
                .addClass('cloned')
                .show()
                .click(function(e)
            {
                e.preventDefault();

                XenForo.ajax(this.href, {}, function(ajaxData, textStatus)
                {
                    if (XenForo.hasResponseError(ajaxData))
                    {
                        return false;
                    }

                    $link.stop(true, true);

                    if (ajaxData.term) // term = Promote / Cancel Promotion
                    {
                        $link.html(ajaxData.term);
                    }
                });
            });
        }

    };

	XenForo.NewsLoader = function ($link)
	{
		if ($($link.data('target')).data('forumid')==$link.data('forumid')) {
                	$link.addClass('selected');
		}

		$link.click(function(e)
		{
			var $news=$($link.data('target')),
				cacheElement=function(e)
				{
					var $e=$(e);
					if (!$e.data('original'))
					{
						$e.data('original', $e.html());
					}
					return $e;
				},
			$desc=cacheElement('p#pageDescription'),
			$title=cacheElement('fieldset.breadcrumb div.boardTitle strong'),
			$h1=cacheElement('div.titleBar h1'),
			isRedirect=false;

			if (!$news.length) {
				$news=$('<div />');
				isRedirect=true;
			}

			if ($link.data('reload'))
			{   //reload is ether filter or forum
                var currentFilter = $news.data('filter')?$news.data('filter'):'top';
                $news.data('filter',($link.data('filter')?$link.data('filter'):currentFilter));
                var currentForumId = $news.data('forumid')?$news.data('forumid'):'';
				$news.data('forumid',(($link.data('forumid')!==null)?$link.data('forumid'):currentForumId));
                $news.data('oldestDate',0);
				$news.children().xfRemove('xfFadeOut');
                $('.readMoreNews a.NewsLoader').show();
                $('a.NewsLoader.'+$link.data('reload')).removeClass('selected');//filter link has class 'filter', forum link has class 'forum'
                $link.addClass('selected');
			}
			else
			{
				if (!$news.data('oldestDate') && $link.data('oldestDate'))
				{
					$news.data('oldestDate',$link.data('oldestDate'));
				}
				if (!$news.data('forumid') && $link.data('forumid'))
				{
					$news.data('forumid',$link.data('forumid'));
				}
                if (!$news.data('filter') && $link.data('filter'))
                {
                    $news.data('filter',$link.data('filter'));
                }
			}

			e.preventDefault();
			if (isRedirect) {
				$('<form method="post" action="'+$link.data('href')+'">'
					+ '<input type="hidden" name="_xfNoRedirect" value="1" />'
					+ '<input type="hidden" name="_xfRequestUri" value="'+window.location.pathname + window.location.search+'" />'
					+ ((XenForo._csrfToken) ? '<input type="hidden" name="_xfToken" value="'+XenForo._csrfToken+'" />' : '')
//					+ '<input type="hidden" name="_xfResponseType" value="json-text" />'
					+ '<input type="hidden" name="oldest_date" value="'+$news.data('oldestDate')+'" />'
					+ '<input type="hidden" name="forum_id" value="'+$news.data('forumid')+'" />'
					+ '<input type="hidden" name="filter" value="'+$news.data('filter')+'" />'
					+ '</form>'
				).appendTo($('body')).submit();
			}

			XenForo.ajax(
				$link.data('href'),
				{ oldest_date: $news.data('oldestDate'), forum_id: $news.data('forumid'), filter: $news.data('filter') },
				function (ajaxData, textStatus)
				{
					if (XenForo.hasTemplateHtml(ajaxData, 'newsTemplateHtml'))
					{
						new XenForo.ExtLoader(ajaxData, function()
						{
							var i=0,
							oldestDate=(ajaxData.oldestDate || 0),
							findOldestDate=!!oldestDate, //true if we have to find oldestDate ourself
							ajaxBlogTitle=ajaxData.title,
							curScroll=$(window).scrollTop();

							$.each(ajaxData.newsTemplateHtml, function(selector, templateHtml)
							{
								var $addNews=$(templateHtml);
								$addNews.xfInsert('appendTo', $news);
								if (findOldestDate)
								{
									oldestDate = $addNews.last().find('a.datePermalink').data('time');
								}
								i++;
							});
							$(window).scrollTop(curScroll);
							if (oldestDate) $news.data('oldestDate',oldestDate);
							if (!i && !$link.data('reload')){$link.hide();}
							document.title=(ajaxBlogTitle) ? (ajaxBlogTitle+' | '+$title.data('original')):$title.data('original');
							$h1.html((ajaxBlogTitle) ? ajaxBlogTitle : $h1.data('original'));
							$desc.html(ajaxData.description);
						});
					}
					else if (XenForo.hasTemplateHtml(ajaxData))
					{
						new XenForo.ExtLoader(ajaxData, function()
						{
							var $addNews=$(ajaxData.templateHtml).find('.message'),
							ajaxBlogTitle=ajaxData.title;
							if ($addNews.length)
							{
								var curScroll=$(window).scrollTop();
								$addNews.xfInsert('appendTo', $news);
								$(window).scrollTop(curScroll);
								var oldestDate = $addNews.last().find('a.datePermalink').data('time');
								if (oldestDate) $news.data('oldestDate',oldestDate);
							}
							else if (!$link.data('reload')) {$link.hide();}
							document.title=(ajaxBlogTitle) ? (ajaxBlogTitle+' | '+$title.data('original')):$title.data('original');
							$h1.html((ajaxBlogTitle) ? ajaxBlogTitle : $h1.data('original'));
							$desc.html(ajaxData.description);
						});
					}
					else
					{
						console.warn('No template HTML!');
						if (!$link.data('reload')){$link.hide();}
					}
				}
			);
		});
	}
	// *********************************************************************

	XenForo.register('a.NewsLoader', 'XenForo.NewsLoader');

    XenForo.register('a.PromotionLink', 'XenForo.PromotionLink');

}
(jQuery, this, document);



