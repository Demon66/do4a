<?php

class CMF_UserBlogs_Listener
{
	public static function loadClassController($class, array &$extend)
	{
		if ($class == 'XenForo_ControllerPublic_Forum')
		{
			$extend[] = 'CMF_UserBlogs_ControllerPublic_Forum';
		}
	}

	public static function loadClassRoutePrefix($class, array &$extend)
	{
		if ($class == 'XenForo_Route_Prefix_Forums')
		{
			$extend[] = 'CMF_UserBlogs_Route_Prefix_Forums';
		}
	}

	public static function loadClassModel($class, array &$extend)
	{
		switch ($class)
		{
			case 'XenForo_Model_Forum':
				$extend[] = 'CMF_UserBlogs_Model_Forum';
				break;

			case 'XenForo_Model_Node':
				$extend[] = 'CMF_UserBlogs_Model_Node';
				break;

			case 'XenForo_Model_Thread':
				$extend[] = 'CMF_UserBlogs_Model_Thread';
				break;
		}
	}
	public static function loadClassView($class, array &$extend)
	{
		switch ($class)
		{
			case 'XenForo_ViewPublic_Forum_View':
				$extend[] = 'CMF_UserBlogs_ViewPublic_Forum_View';
				break;
		}
	}
}
