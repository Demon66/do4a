<?php

class Social_Helper_News
{
	public static function prepareNewsPost($post)
	{
		$xenOptions = XenForo_Application::get('options');
		$post = str_ireplace(array('[cut][/cut]', '[cut]end[/cut]'), array('[plain][/plain]', '[cut] [/cut][plain] [/plain]'), $post);

		if (($offset = stripos($post, '[plain] [/plain]')) !== false) $post = substr($post, 0, $offset);
		if (($offset = stripos($post, '[plain][/plain]')) !== false) {
			if (sizeof($newsArray = explode('[plain][/plain]', $post)) > 1) {
				$post = '';
				$i = 0;
				foreach ($newsArray as $newsChunk)
				{
					if (($i++) % 2 == 0) $post .= $newsChunk;
				}
			}

		}

		if (stripos($post, '[cut]') === false) {
			$post = XenForo_Helper_String::wholeWordTrim($post, $xenOptions->socialCutAuto);
		}
		$post = preg_replace('/\s*\n{3,}\s*/', "\n\n", $post);
		return $post;
	}
}