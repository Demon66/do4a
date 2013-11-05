<?php

/**
 * Model for threads.
 *
 * @package XenForo_Thread
 */
class CMF_Core_Model_Thread extends XFCP_CMF_Core_Model_Thread
{
	/**
	 * Standard approach to caching prepareThread callbacks.
	 *
	 * @var array
	 */
	protected $_callbacksCache = array();

	public function prepareThreadFetchOptions(array $fetchOptions)
	{
		$threadFetchOptions = parent::prepareThreadFetchOptions($fetchOptions);

		if ($cmfThreadFields=array_keys(CMF_Core_Application::getCMF()->get('dw_fields', 'thread')))
		{
			$threadFetchOptions['selectFields'] .= ',
					cmf_thread.'. implode(', cmf_thread.', $cmfThreadFields);

			$threadFetchOptions['joinTables'] .= '
					LEFT JOIN cmf_thread AS cmf_thread ON
							(thread.thread_id = cmf_thread.thread_id)';
		}

		return $threadFetchOptions;
	}
}