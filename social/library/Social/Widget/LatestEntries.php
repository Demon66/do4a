<?php

class Social_Widget_LatestEntries extends Social_Widget_LatestThreads
{

	protected function _constructSetup(array &$viewParams = array())
	{
		$this->_widget['type'] = 'blog';
		$this->_widget['titlePhraseName'] = 'social_latest_blog_entries';
		parent::_constructSetup($viewParams);
	}

}