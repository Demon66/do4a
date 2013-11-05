<?php

/**
 * Controller for handling actions on forums.
 *
 * @package XenForo_Forum
 */
class Social_ControllerPublic_Forum extends XFCP_Social_ControllerPublic_Forum
{

	public function getHelper($class)
	{
		if ($class == 'XenForo_ControllerHelper_ForumThreadPost' || $class == 'ForumThreadPost')
        {
			$class = 'Social_ControllerHelper_ForumThreadPost';
		}

		return parent::getHelper($class);
	}

}