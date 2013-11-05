<?php

/**
 * Controller for handling actions on forums.
 *
 * @package XenForo_Forum
 */

class CMF_Core_ControllerPublic_Forum extends XFCP_CMF_Core_ControllerPublic_Forum
{

	public function actionAddThread()
	{
		$cmf=CMF_Core_Application::getCMF();
		$cmf->set('dw_data', 'thread',
			$this->_input->filter(
				$cmf->get('input_fields', 'thread')
			)
		);
		return parent::actionAddThread();
	}
}