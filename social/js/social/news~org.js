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
			$h1=cacheElement('div.titleBar h1');

			if ($link.data('reload'))
			{   //reload is ether filter or forum
                var currentFilter = $news.data('filter')?$news.data('filter'):'top';
                $news.data('filter',($link.data('filter')?$link.data('filter'):currentFilter));
                var currentForumId = $news.data('forumId')?$news.data('forumId'):'';
				$news.data('forumId',(($link.data('forumId')!==null)?$link.data('forumId'):currentForumId));
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
				if (!$news.data('forumId') && $link.data('forumId'))
				{
					$news.data('forumId',$link.data('forumId'));
				}
                if (!$news.data('filter') && $link.data('filter'))
                {
                    $news.data('filter',$link.data('filter'));
                }
			}

			e.preventDefault();

			XenForo.ajax(
				$link.data('href'),
				{ oldest_date: $news.data('oldestDate'), forum_id: $news.data('forumId'), filter: $news.data('filter') },
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