<?php


class Social_EventListener_Extend
{
	public static function loadClassModel($class, array &$extend)
	{
		if ($class == 'XenForo_Model_Node')
        {
			$extend[] = 'Social_Model_Node';
		}

		if ($class == 'XenForo_Model_Forum')
        {
			$extend[] = 'Social_Model_Forum';
		}

		if ($class == 'XenForo_Model_Thread')
        {
			$extend[] = 'Social_Model_Thread';
		}

        if ($class == 'XenForo_Model_NewsFeed')
        {
        	$extend[] = 'Social_Model_NewsFeed';
        }
	}

	public static function loadClassController($class, array &$extend)
	{
		if ($class == 'XenForo_ControllerAdmin_Forum')
        {
			$extend[] = 'Social_ControllerAdmin_Forum';
		}

		if ($class == 'XenForo_ControllerPublic_Forum')
        {
			$extend[] = 'Social_ControllerPublic_Forum';
		}

		if ($class == 'XenForo_ControllerPublic_Thread')
        {
			$extend[] = 'Social_ControllerPublic_Thread';
		}

		if ($class == 'XenForo_ControllerPublic_Misc')
        {
			$extend[] = 'Social_ControllerPublic_Misc';
		}
	}

	public static function loadClassDataWriter($class, array &$extend)
	{
		if ($class == 'XenForo_DataWriter_Forum')
        {
			$extend[] = 'Social_DataWriter_Forum';
		}

		if ($class == 'XenForo_DataWriter_DiscussionMessage_Post')
        {
			$extend[] = 'Social_DataWriter_DiscussionMessage_Post';
		}

        if ($class == 'XenForo_DataWriter_Discussion_Thread')
        {
            $extend[] = 'Social_DataWriter_Discussion_Thread';
        }

		if ($class == 'XenForo_DataWriter_Option')
        {
			$extend[] = 'Social_DataWriter_Option';
		}
	}

	public static function loadClassView($class, array &$extend)
	{
		if ($class == 'XenForo_ViewPublic_Category_View')
        {
			$extend[] = 'Social_ViewPublic_Category_View';
		}

		if ($class == 'XenForo_ViewPublic_Thread_View')
        {
			$extend[] = 'Social_ViewPublic_Thread_View';
		}

		if ($class == 'XenForo_ViewPublic_Thread_ViewNewPosts')
        {
			$extend[] = 'Social_ViewPublic_Thread_ViewNewPosts';
		}

		if ($class == 'XenForo_ViewPublic_Thread_Reply')
        {
			$extend[] = 'Social_ViewPublic_Thread_Reply';
		}

		if ($class == 'XenForo_ViewPublic_Thread_ViewPosts')
        {
			//$extend[] = 'Social_ViewPublic_Thread_ViewPosts';
		}
	}

	public static function loadClassBbCode($class, array &$extend)
	{
		if ($class == 'XenForo_BbCode_Formatter_Base')
        {
			$extend[] = 'Social_BbCode_Formatter_Base';
		}

		if ($class == 'XenForo_BbCode_Formatter_Text')
        {
			$extend[] = 'Social_BbCode_Formatter_Text';
		}
	}

	public static function loadClassRoutePrefix($class, array &$extend)
	{
		if (XenForo_Application::get('options')->socialNewsIndex)
        {
			if ($class == 'XenForo_Route_Prefix_Categories')
            {
				$extend[] = 'Social_Route_Prefix_Categories';
			}
			else if ($class == 'XenForo_Route_Prefix_Index')
            {
				$extend[] = 'Social_Route_Prefix_Index';
			}
		}
	}

    public static function loadClassSearchData($class, array &$extend)
    {
        if ($class == 'XenForo_Search_DataHandler_Post')
        {
            $extend[] = 'Social_Search_DataHandler_Post';
        }

        else if ($class == 'XenForo_Search_DataHandler_Thread')
        {
            $extend[] = 'Social_Search_DataHandler_Thread';
        }
    }
}
