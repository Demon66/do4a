<?php

/**
* Helper methods for the core template functions/tags.
*
* @package XenForo_Template
*/
class CMF_BbCodes_Template_Helper_Core extends XenForo_Template_Helper_Core
{
	public static $parentHelper = null;
	/**
	 * Strips BB Code from a string and word-trims it to a given max length around an optional search term
	 *
	 * @param string $string Input text (bb code)
	 * @param integer $maxLength
	 * @param array $options Key-value options
	 *
	 * @return string HTML
	 */
	public static function helperSnippet($string, $maxLength = 0, array $options = array())
	{
		$fixMD = preg_match('#\[md\]|\[code=markdown\]#siU', $string) ? true : false;

		if (self::$parentHelper)
		{
//			list($class,$callback)=self::$parentHelper;
//			if ($class=='self')
			if (self::$parentHelper[0]=='self')
			{
				$string = call_user_func(array('parent',self::$parentHelper[1]), $maxLength, $options);
//				$string = parent::$callback($string, $maxLength, $options);
	
			}
			else
			{
				$string = call_user_func(self::$parentHelper, $maxLength, $options);
				//	$string = $class::$callback($string, $maxLength, $options);
			}
		}
		else
		{
			$string = parent::helperSnippet($string, $maxLength, $options);
		}
		return ($fixMD) ? preg_replace(array(
				'/(^|[^\pL])([#_*]+|[-=]{4,})/u',
				'/[#*_]+($|[^\pL])/u'
//				'/[#*_]+($|[\r\n\t ]|[^\pL])/u'
			), '\1',  $string ) : $string;
	}
}