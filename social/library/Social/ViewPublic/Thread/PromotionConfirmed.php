<?php

class Social_ViewPublic_Thread_PromotionConfirmed extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
        $output = array('templateHtml' => '', 'js' => '', 'css' => '');

        if ($this->_params['promotionDate']>0)
        {
            $output['term'] = new XenForo_Phrase('social_cancel_promotion');
        }
        else
        {
            $output['term'] = new XenForo_Phrase('social_promote_thread');
        }

		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}