<?php

class Social_ViewPublic_Helper_News extends XenForo_ViewPublic_Helper_Message
{
	public static function getBbCodeWrapper(array &$message, XenForo_BbCode_Parser $parser, array $options = array())
	{
		$xenOptions = XenForo_Application::get('options');
/*
		$message['message'] = str_ireplace(array('[cut][/cut]', '[cut]end[/cut]'), array('[plain][/plain]', '[plain] [/plain]'), $message['message']);

		if (($offset = stripos($message['message'], '[plain] [/plain]')) !== false) $message['message'] = substr($message['message'], 0, $offset);
		if (($offset = stripos($message['message'], '[plain][/plain]')) !== false) {
			if (sizeof($newsArray = explode('[plain][/plain]', $message['message'])) > 1) {
				$message['message'] = '';
				$i = 0;
				foreach ($newsArray as $newsChunk)
				{
					if (($i++) % 2 == 0) $message['message'] .= $newsChunk;
				}
			}

		}


		if (stripos($message['message'], '[cut]') === false) {
			$message['message'] = XenForo_Helper_String::wholeWordTrim($message['message'], $xenOptions->socialCutAuto);
			if ($xenOptions->socialMaxMediaInNews) {
				$options['states']['maxMedia'] = $xenOptions->socialMaxMediaInNews;
			}
		}
*/
//		$message['message'] = preg_replace('/\s*\n{3,}\s*/', "\n\n", $message['message']);

		$options['states']['newsView'] = true;
		if ($xenOptions->socialMaxMediaInNews && (stripos($message['message'], '[cut]') === false)) {
			$options['states']['maxMedia'] = $xenOptions->socialMaxMediaInNews;
		}

		if ($xenOptions->socialAutoCenterMedia) {
			$options['states']['autoCenter'] = true;
		}

		return parent::getBbCodeWrapper($message, $parser, $options);
	}

	public static function bbCodeWrapMessages(array &$messages, XenForo_BbCode_Parser $parser, array $options = array())
	{
		$options += array(
			'showSignature' => XenForo_Visitor::getInstance()->get('content_show_signature'),
			'states' => array()
		);

		foreach ($messages AS &$message)
		{
			$message['messageHtml'] = Social_ViewPublic_Helper_News::getBbCodeWrapper($message, $parser, $options);
		}
	}
}