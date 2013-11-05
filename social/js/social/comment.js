//noinspection BadExpressionStatementJS
!function($, window, document, _undefined)
{
	XenForo.InsertAndScroll = function(e, $form)
    {

        new XenForo.ExtLoader(e.ajaxData, function()
        {
            var insertedPosts = 0;

            $.each(e.ajaxData.posts, function(postId, post){

                var scrollStarted = 0;
                var parentPostId= post.parent_post_id;

                if(parentPostId && !$('#children-'+parentPostId).length){
                    var childrenContainer=$('<ol></ol>').attr('id', 'children-'+parentPostId).addClass('comments').addClass('children');
                    $(childrenContainer).xfInsert('insertAfter', '#post-'+parentPostId);
                }

                var container = '#children-'+parentPostId;

                $(post.templateHtml).xfInsert('appendTo', container, 'xfFadeIn', XenForo.speed.xxfast,function(){

                    if(postId==e.ajaxData.lastPost.post_id){

                        console.info("Last post was inserted.");

                        if(e.ajaxData.newMessagesNoticeHtml){
                            $(e.ajaxData.newMessagesNoticeHtml).xfInsert('appendTo', container, 'xfFadeIn', XenForo.speed.xxfast);
                        }

                        var target = $('#post-' + e.ajaxData.lastPost.post_id).offset().top - 100;// - $(window).height()/3,
                            scroller = XenForo.getPageScrollTagName();
                        $(scroller).animate({ scrollTop: target }, XenForo.speed.slow);
                    }

                });
            });

            $form.find('textarea').val('');

            $('#QuickReply input[name=last_date]').val(e.ajaxData.lastDate);

            if (window.tinyMCE)
            {
                window.tinyMCE.editors['ctrl_message_html'].focus();
            }
            else
            {
                $('#QuickReply').find('textarea:first').get(0).focus();
            }

            $form.get(0).reset();
            if ($form.parents('.xenOverlay').length)
            {
                $form.parents('.xenOverlay').data('overlay').close();
            }
        });
    };

    //reply with no parent
    XenForo.InsertQuickReply = function($form)
    {
        $form.bind('AutoValidationComplete', function(e)
        {
             e.preventDefault();
             XenForo.InsertAndScroll(e , $form);
        });

    };

    //reply with parent
    XenForo.InsertReply = function($form)
    {
        $form.bind('AutoValidationBeforeSubmit', function(e)
        {
            if(!$form.find('#QuickReply input[name=last_date]').length){
                $('#QuickReply input[name=last_date]').clone().appendTo($form); // takes the last date from Quick Reply
            }
        });


        $form.bind('AutoValidationComplete', function(e)
        {
            e.preventDefault();
            $form.find('input[name=last_date]').remove(); //removes used last date
            XenForo.InsertAndScroll(e , $form);
        });
    };

    XenForo.register('#QuickReply', 'XenForo.InsertQuickReply');
	XenForo.register('#Reply', 'XenForo.InsertReply');
}
(jQuery, this, document);


