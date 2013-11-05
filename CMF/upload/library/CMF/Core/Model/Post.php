<?php

/**
 * Model for posts.
 *
 * @package XenForo_Post
 */
class CMF_Core_Model_Post extends XFCP_CMF_Core_Model_Post
{
	/**
	 * Array of applied callbacks Array(post_id => callbacks_array, post_id => callbacks_array ...)
	 *
	 * @var array
	 */
	protected $_callbacksApplied = array();

	/**
	 * Type of PostCache for fetching and saving
	 *
	 * @var string
	 */
	protected $_postCacheType = '';

	/**
	 * Set PostCache type for model
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function setPostCacheType($type)
	{
		return ($this->_postCacheType = strval($type));
	}

	/**
	 * Checks the 'join' key of the incoming array for the presence of the FETCH_x bitfields in this class
	 * and returns SQL snippets to join the specified tables if required
	 *
	 * @param array $fetchOptions containing a 'join' integer key build from this class's FETCH_x bitfields
	 *
	 * @return array Containing 'selectFields' and 'joinTables' keys. Example: selectFields = ', user.*, foo.title'; joinTables = ' INNER JOIN foo ON (foo.id = other.id) '
	 */
	public function preparePostJoinOptions(array $fetchOptions)
	{
		$return = parent::preparePostJoinOptions($fetchOptions);
		if ($this->_postCacheType)
		{
			$return['selectFields'] .= ',
				post_cache.* ';
			$return['joinTables'] .= '
				LEFT JOIN cmf_post_cache AS post_cache ON
					(post_cache.post_id = post.post_id AND post_cache.cache_type = ' . $this->_getDb()->quote($this->_postCacheType) . ')';
		}
		return $return;
	}

	/**
	 * Prepares a post for display, generally within the context of a thread.
	 *
	 * @param array $post Post to prepare
	 * @param array $thread Thread post is in
	 * @param array $forum Forum thread/post is in
	 * @param array|null $nodePermissions
	 * @param array|null $viewingUser
	 *
	 * @return array Prepared version of post
	 */
	public function preparePost(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		$post = parent::preparePost($post, $thread, $forum, $nodePermissions, $viewingUser);
		$callbacks = $this->_getPostCallbacks($post, $thread, $forum, $nodePermissions, $viewingUser);
		//Execute callbacks if not empty
		if ($callbacks)
		{
			ksort($callbacks);
			$post = $this->applyPostCallbacks($callbacks, $post, $thread, $forum, $nodePermissions, $viewingUser);
		}

		$callbacks = $this->_getPostCacheCallbacks($post, $thread, $forum, $nodePermissions, $viewingUser);

		if ($callbacks && $this->_postCacheType)
		{
			ksort($callbacks);

			if ($this->isValidPostCache($callbacks, $post, $thread, $forum))
			{
				$post['message'] = $post['post_cache'];
			}
			else
			{
				$post = $this->applyPostCallbacks($callbacks, $post, $thread, $forum, $nodePermissions, $viewingUser);
				$dw = XenForo_DataWriter::create('CMF_Core_DataWriter_PostCache');
				$dw->setExistingData(
					array(
						'post_id' => $post['post_id'],
						'cache_type' => strval($this->_postCacheType),
					)
				);
				$dw->set('cache_date', XenForo_Application::$time);
				$dw->set('post_cache', ($post['message'] !== '') ? $post['message'] : ' '); //save not empty string
				$dw->set('cache_filters', implode('_', $this->_callbacksApplied[$post['post_id']]));
				$dw->save();
			}
		}
		return $post;
	}

	/**
	 * Prepares a post for display applying CMF callbacks, generally within the context of a specific forum.
	 *
	 * @param array $callbacks Callbacks array
	 * @param array $post Post to prepare
	 * @param array $thread Thread to prepare
	 * @param array $forum Forum thread is in
	 * @param array|null $nodePermissions
	 * @param array|null $viewingUser
	 *
	 * @return array Prepared version of thread
	 */
	final public function applyPostCallbacks(array $callbacks, array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		if (!isset($this->_callbacksApplied[$post['post_id']]))
		{
			$this->_callbacksApplied[$post['post_id']] = array();
		}
		if ($callbacks)
		{
			foreach ($callbacks as $callback)
			{
				$name = ((isset($callback['name']))) ? strval($callback['name']) : false ;
				if (isset($callback['callback'])) {
					$callback = $callback['callback'];
				}
				if (is_callable($callback))
				{
					$post = call_user_func($callback, $post, $thread, $forum, $nodePermissions, $viewingUser);
					if ($name !== false)
					{
						$this->_callbacksApplied[$post['post_id']][] = $name;
					}
				}
			}
		}
		return $post;
	}

	/**
	 * Returns preparePost Callbacks Array, generally within the context of a specific forum and thread.
	 * for extending by submodules
	 *
	 * @param array $post Post to prepare
	 * @param array $thread Thread post is in
	 * @param array $forum Forum thread/post is in
	 * @param array|null $nodePermissions
	 * @param array|null $viewingUser
	 *
	 * @return array prepareThread Callbacks Array
	 */
	protected function _getPostCallbacks(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		//XenForo_CodeEvent::fire('cmf_prepare_post_callbacks', array($this, $post, $thread, $forum, $nodePermissions, $viewingUser));
		return array();
	}

	/**
	 * Returns preparePost Callbacks Array for PostCache, generally within the context of a specific forum and thread.
	 * for extending by submodules
	 *
	 * @param array $post Post to prepare
	 * @param array $thread Thread post is in
	 * @param array $forum Forum thread/post is in
	 * @param array|null $nodePermissions
	 * @param array|null $viewingUser
	 *
	 * @return array prepareThread Callbacks Array
	 */
	protected function _getPostCacheCallbacks(array $post, array $thread, array $forum, array $nodePermissions = null, array $viewingUser = null)
	{
		return array();
	}

	/**
	 * Validate postCache by TTL, cache_filters...
	 *
	 * @param array $callbacks PostCache Callbacks array
	 * @param array $post Post to validate
	 * @param array $thread Thread post is in
	 * @param array $forum Forum thread/post is in
	 *
	 * @return boolean
	 */
	public function isValidPostCache(array $callbacks, array $post, array $thread, array $forum)
	{
		if ($post['post_cache']==='')
		{
			return false;
		}
		$filters = $this->_callbacksApplied[$post['post_id']];
		foreach ($callbacks as $callback)
		{
			if (isset($callback['name']))
			{
				$filters[] = $callback['name'];
			}
		}
		if ($post['cache_filters'] !== implode('_', $filters))
		{
			return false;
		}
		//TODO TTL validator
		return true;
	}

	/**
	 * Gets a post cache by ID and cache type.
	 *
	 * @param integer $postId
	 * @param string $cacheType
	 *
	 * @return array|boolean array|false
	 */
	public function getPostCacheByTypeAndId($postId, $cacheType)
	{
		return $this->_getDb()->fetchRow('
			SELECT *
			FROM cmf_post_cache
			WHERE post_id = ? AND cache_type = ?
		', array($postId, $cacheType));
	}
	/**
	 * Gets a post cache by ID and cache type.
	 *
	 * @param integer $postId
	 *
	 * @return null
	 */
	public function deletePostCacheById($postId)
	{
		$this->_getDb()->query('
			DELETE FROM cmf_post_cache
			WHERE post_id = ?
		', $postId);
	}

}