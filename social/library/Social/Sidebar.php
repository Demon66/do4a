<?php

class Social_Sidebar
{

    public static $controller;
	public static $widgets = array();


	/**
	 * @static
	 * @param XenForo_ControllerPublic_Abstract $controller
	 * @param $widgetsConfig
	 *
	 * Initialize widgets from config. self::widgets consists of $class => array $usage
	 */
	public static function setup($controller, $action, $section)
	{
		require(XenForo_Application::getInstance()->getRootDir() . '/library/Social/config.php');
		$widgetsConfig = !empty($config['widgets']) ? $config['widgets'] : array();

		$widgetWeights = array();

		foreach ($widgetsConfig as $class => &$usages)
		{
			if (!empty(Social_Sidebar::$widgets[$class]) && (Social_Sidebar::$widgets[$class] instanceof Social_Widget_Abstract)) {
				/* widget is already prepared in controller.
                * no need to load it from config since conroller is more important
                */
				if (!empty($usages['change']) && is_array($usages['change'])) {
					foreach ($usages['change'] as $key => $value) {
						Social_Sidebar::$widgets[$class][$key]=$value;
					}
				}
				continue;
			}
			unset ($usages['change']);
			foreach ($usages as &$usage)
			{
				if (empty($usage['controller']) || (empty($usage['action']) && empty($usage['section']))) {
					continue;
				}

				if (strpos($usage['controller'], '_') === false) {
					$usage['controller'] = 'XenForo_ControllerPublic_' . $usage['controller'];
				}

				$controllerMatch = intval($usage['controller'] == $controller);
				$weight = $controllerMatch;

				$actionMatch = !empty($usage['action']) ? intval($usage['action'] == $action) : 1;
				$weight *= 3 * $actionMatch;

				$sectionMatch = !empty($usage['section']) ? intval($usage['section'] == $section) : 1;
				$weight *= 2 * $sectionMatch;

				$oldWeight = !empty($widgetWeights[$class]) ? $widgetWeights[$class] : 1;

				if ($weight > $oldWeight) {
					$widgetWeights[$class] = $weight;
					Social_Sidebar::$widgets[$class] = $usage;
				}
			}

		}


	}

	/**
	 * @static
	 * @param XenForo_ControllerPublic_Abstract $controller
	 * @param array $viewParams
	 *
	 * self::widgets transform $class => array $usage to $class => Social_Widget_Abstract $widget
	 */
	public static function prepareWidgets(array $viewParams = array())
	{
		foreach (self::$widgets as $class => &$widget)
		{
			if (XenForo_Application::autoload($class) && is_array($widget)) {
				$widget = Social_Widget_Abstract::create($class, $viewParams, $widget);
			}
		}
	}

	/**
	 * @static
	 *
	 * Sorts by position
	 */
	public static function sortWidgets()
	{
		$widgetPositions = array();

		foreach (self::$widgets as $key => $widget)
		{
			if (!empty($widget['position'])) {
				$widgetPositions[$key] = $widget['position'];
			}
			else
			{
				$widgetPositions[$key] = 1000;
			}
		}

		asort($widgetPositions);

		$widgets = array();
		foreach ($widgetPositions as $key => $position)
		{
			$widgets[$key] = self::$widgets[$key];
		}

		self::$widgets = $widgets;
	}

	public static function renderSidebar(XenForo_Template_Abstract $template, array &$params = array())
	{
		Social_Sidebar::sortWidgets();

		$template->preloadTemplate('PAGE_CONTAINER');

		foreach (Social_Sidebar::$widgets as $widget)
		{

			$template->preloadTemplate($widget['templateName']);
		}

		$sidebarHtml = '';

		foreach (Social_Sidebar::$widgets as $widget)
		{
			if (!empty($widget['sidebar']) && ($sidebarVar=$widget['sidebar'])!=='sidebar') {
				//only for string and not existed params
				if (!isset($params[$sidebarVar])) {
					$params[$sidebarVar]='';
				}
				if (is_string($params[$sidebarVar])) {
					$params[$sidebarVar] .= $template->create($widget['templateName'], array_merge($params, $widget['params']))->render();
				}
			}
			else { 
				$sidebarHtml .= $template->create($widget['templateName'], array_merge($params, $widget['params']))->render();
			}
		}

		return $sidebarHtml;
	}
}