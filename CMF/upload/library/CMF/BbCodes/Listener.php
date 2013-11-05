<?php

class CMF_BbCodes_Listener
{
	public static $validFilters=array(
		'h100' => array(0,100,true),
		'h150' => array(0,150,true),
		'h200' => array(0,200),
		'h300' => array(0,300),
		'w200' => array(200,0,true),
		'w400' => array(400,0),
		'w600' => array(600,0),
		'w800' => array(800,0)
	);

	public static function loadClassBbCode($class, array &$extend)
	{
		if ($class == 'XenForo_BbCode_Formatter_Wysiwyg')
		{	
			$extend[] = 'CMF_BbCodes_BbCode_Formatter_Wysiwyg';
		}
		if ($class == 'XenForo_BbCode_Formatter_Base')
		{
			$extend[] = 'CMF_BbCodes_BbCode_Formatter_Base';
		}
		if ($class == 'XenForo_BbCode_Formatter_Text')
		{
			$extend[] = 'CMF_BbCodes_BbCode_Formatter_Text';
		}
	}
	public static function loadClassController($class, array &$extend)
	{
		if ($class == 'XenForo_ControllerPublic_Attachment')
		{
			$extend[] = 'CMF_BbCodes_ControllerPublic_Attachment';
		}
	}
	public static function loadClassModel($class, array &$extend)
	{

		if ($class == 'XenForo_Model_Attachment')
		{
			$extend[] = 'CMF_BbCodes_Model_Attachment';
		}
	}
	public static function loadClassRoutePrefix($class, array &$extend)
	{
		if ($class == 'XenForo_Route_Prefix_Attachments')
		{
			$extend[] = 'CMF_BbCodes_Route_Prefix_Attachments';
		}
	}
	public static function loadClassDataWriter($class, array &$extend)
	{
		if ($class == 'XenForo_DataWriter_AttachmentData')
		{
			$extend[] = 'CMF_BbCodes_DataWriter_AttachmentData';
		}
	}
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		CMF_BbCodes_Template_Helper_Core::$parentHelper=XenForo_Template_Helper_Core::$helperCallbacks['snippet'];
		XenForo_Template_Helper_Core::$helperCallbacks['snippet'] = array('CMF_BbCodes_Template_Helper_Core', 'helperSnippet');
	}
}
