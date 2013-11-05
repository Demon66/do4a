<?php

/**
* Helper methods for the core template functions/tags.
*
* @package XenForo_Template
*/
class CMF_Core_Template_Helper_Core
{
	/**
	 * Array to cache model objects
	 *
	 * @var array
	 */
	protected static $_modelCache = array();

	/**
	 * Returns a formatted tag string with search links.
	 *
	 * @param string|array $tagSet
	 * @param array $options
	 * @param array $attributes
	 *
	 * @return string
	 */
	public static function helperTagsHtml($tagSet, $options = null, $attributes = null)
	{
		if (!is_array($attributes))
		{
			$attributes=array();
		}
		if (!is_array($options))
		{
			$options=array();
		}
		$options = array_merge(array(
			'term' => '',
			'emClass' => ''
		), $options);

		/** @var $tagModel CMF_Core_Model_Tag */
		$tagModel = self::_getModelFromCache('CMF_Core_Model_Tag');
		if (!is_array($tagSet))
		{
			$tagSet = $tagModel->getTagsArrayFromSet($tagSet);
		}
		if (empty($tagSet))
		{
			return '';
		}

		$tagsHtmlArray=Array();

		foreach ($tagSet as $tag)
		{
			$tagHtml = '';
			$attr = $attributes;
			$link = XenForo_Link::buildPublicLink('search/search', null, array('keywords' => $tag, 'type' => 'tag'));
//			$class = (empty($attr['class']) ? '' : htmlspecialchars($attr['class']));
//			unset($attr['class']);

			if (!empty($attr['tag']))
			{
				$wrapTag = $attr['tag'];
				unset($attr['tag']);
			}
			else
			{
				$wrapTag='';
			}
			$attrString = XenForo_Template_Helper_Core::getAttributes($attr);

			$tag = ($options['term']) ? XenForo_Helper_String::highlightSearchTerm($tag, $options['term'], $options['emClass']) : htmlspecialchars($tag);

//			$tagHtml = "<a href=\"{$link}\" class=\"{$class}\" {$attrString}>" . $tag . "</a>";
			$tagHtml = "<a href=\"{$link}\" {$attrString}>" . $tag . "</a>";

			$tagsHtmlArray[] = (!empty($wrapTag)) ? "<$wrapTag>$tagHtml</$wrapTag>" : $tagHtml;
		}
		return implode(', ', $tagsHtmlArray);
	}
	/**
	 * Fetches a model object from the local cache
	 *
	 * @param string $modelName
	 *
	 * @return XenForo_Model
	 */
	protected static function _getModelFromCache($modelName)
	{
		if (!isset(self::$_modelCache[$modelName]))
		{
			self::$_modelCache[$modelName] = XenForo_Model::create($modelName);
		}

		return self::$_modelCache[$modelName];
	}

}