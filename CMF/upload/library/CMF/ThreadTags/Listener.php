<?php

class CMF_ThreadTags_Listener
{
	public static function loadClassSearchData($class, array &$extend)
	{
		if ($class == 'XenForo_Search_DataHandler_Thread')
		{
			$extend[] = 'CMF_ThreadTags_Search_DataHandler_Thread';
		}
	}
	public static function initDependencies(XenForo_Dependencies_Abstract $dependencies, array $data)
	{
		//TODO Move to front_controller_pre_dispatch with conditions
		$cmf = CMF_Core_Application::getCMF();
		$cmf->setTagContentTypes('thread');
		$cmf->set('dw_fields', 'thread', array(
			'tags' => array('verification' => array('$this', '_prepareTagSet'), 'type' => XenForo_DataWriter::TYPE_STRING, 'maxLength' => 250)
		));
		$cmf->set('input_fields', 'thread', array(
			'tags' => XenForo_Input::STRING
		));

	}

	public static function loadClassDataWriter($class, array &$extend)
	{
		if ($class == 'XenForo_DataWriter_Discussion_Thread')
		{
			$extend[] = 'CMF_ThreadTags_DataWriter_Thread';
		}
	}
}