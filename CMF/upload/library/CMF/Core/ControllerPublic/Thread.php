<?php

/**
 * Controller for handling actions on threads.
 *
 * @package XenForo_Thread
 */

class CMF_Core_ControllerPublic_Thread extends XFCP_CMF_Core_ControllerPublic_Thread
{

	public function actionSave()
	{
		$cmf=CMF_Core_Application::getCMF();
		$cmf->set('dw_data', 'thread',
			$this->_input->filter(
				$cmf->get('input_fields', 'thread')
			)
		);
		return parent::actionSave();
	}
}