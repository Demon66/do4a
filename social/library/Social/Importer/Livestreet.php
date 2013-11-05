<?php

class Social_Importer_Livestreet extends XenForo_Importer_Abstract
{
	/**
	 * Source database connection.
	 *
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_sourceDb;

	protected $_prefix;

	protected $_charset = 'utf8';

	protected $_config;

	protected $_groupMap = null;

	protected $_userFieldMap = null;

	public static function getName()
	{
		return 'Livestreet';
	}

	public function configure(XenForo_ControllerAdmin_Abstract $controller, array &$config)
	{
		if ($config) {
			$errors = $this->validateConfiguration($config);
			if ($errors) {
				return $controller->responseError($errors);
			}

			$this->_bootstrap($config);

			return true;
		}
		else
		{

			$configPath = getcwd() . '/includes/config.php';
			if (file_exists($configPath) && is_readable($configPath)) {
				$config = array();
				include($configPath);

				$viewParams = array('input' => $config);
			}
			else
			{
				$viewParams = array('input' => array
				(
					'MasterServer' => array
					(
						'servername' => 'localhost',
						'port' => 3306,
						'username' => '',
						'password' => '',
					),
					'Database' => array
					(
						'dbname' => '',
						'tableprefix' => 'prefix_'
					),
				));
			}

			return $controller->responseView('XenForo_ViewAdmin_Import_Livestreet_Config', 'import_livestreet_config', $viewParams);
		}
	}

	public function validateConfiguration(array &$config)
	{
		$errors = array();

		$config['db']['prefix'] = preg_replace('/[^a-z0-9_]/i', '', $config['db']['prefix']);

		try
		{
			$db = Zend_Db::factory('mysqli',
				array(
					'host' => $config['db']['host'],
					'port' => $config['db']['port'],
					'username' => $config['db']['username'],
					'password' => $config['db']['password'],
					'dbname' => $config['db']['dbname'],
					'charset' => 'utf8'
				)
			);
			$db->getConnection();
		}
		catch (Zend_Db_Exception $e)
		{
			$errors[] = new XenForo_Phrase('source_database_connection_details_not_correct_x', array('error' => $e->getMessage()));
		}

		if ($errors) {
			return $errors;
		}

		try
		{
			$db->query('
				SELECT user_id
				FROM ' . $config['db']['prefix'] . 'user
				LIMIT 1
			');
		}
		catch (Zend_Db_Exception $e)
		{
			if ($config['db']['dbname'] === '') {
				$errors[] = new XenForo_Phrase('please_enter_database_name');
			}
			else
			{
				$errors[] = new XenForo_Phrase('table_prefix_or_database_name_is_not_correct');
			}
		}

		$blogsRoot = Social_Helper_Node::getTypeRootFromCache('blog');
		if (!isset($blogsRoot['node_id'])) {
			$errors[] = new XenForo_Phrase('blog_root_not_found');
		}

        $personalBlogsRoot = XenForo_Model::create('XenForo_Model_Forum')->getForumById(XenForo_Application::getOptions()->get('socialPersonalBlogRoot'));
        if (!isset($personalBlogsRoot['node_id'])) {
            $errors[] = new XenForo_Phrase('personal_blog_root_not_found');
        }

		return $errors;
	}

	public function getSteps()
	{
		return array(
			'users' => array(
				'title' => new XenForo_Phrase('import_users'),
			),

			'blogs' => array(
				'title' => new XenForo_Phrase('social_import_blogs'),
				'depends' => array('users')
			),

			'topics' => array(
				'title' => new XenForo_Phrase('social_import_topics'),
				'depends' => array('blogs', 'users')
			),
			'comments' => array(
				'title' => new XenForo_Phrase('social_import_comments'),
				'depends' => array('topics', 'blogs', 'users')
			),

		);
	}

	protected function _bootstrap(array $config)
	{
		if ($this->_sourceDb) {
			return;
		}

		@set_time_limit(0);

		$this->_config = $config;

		$this->_sourceDb = Zend_Db::factory('mysqli',
			array(
				'host' => $config['db']['host'],
				'port' => $config['db']['port'],
				'username' => $config['db']['username'],
				'password' => $config['db']['password'],
				'dbname' => $config['db']['dbname'],
				'charset' => 'utf8'
			)
		);

		$this->_prefix = preg_replace('/[^a-z0-9_]/i', '', $config['db']['prefix']);
	}

	public function configStepUsers(array $options)
	{
		if ($options) {
			return false;
		}

		return $this->_controller->responseView('XenForo_ViewAdmin_Import_vBulletin_ConfigUsers', 'import_config_users');
	}

	public function stepUsers($start, array $options)
	{
		$options = array_merge(array(
			'limit' => 100,
			'max' => false,
			// all checkbox options must default to false as they may not be submitted
			'mergeEmail' => false,
			'mergeName' => false,
			'gravatar' => false
		), $options);

		$sDb = $this->_sourceDb;
		$prefix = $this->_prefix;

		/* @var $model XenForo_Model_Import */
		$model = $this->_importModel;

		if ($options['max'] === false) {
			$options['max'] = $sDb->fetchOne('
				SELECT MAX(user_id)
				FROM ' . $prefix . 'user
			');
		}

		$users = $sDb->fetchAll(
			$sDb->limit($this->_getSelectUserSql('user.user_id > ' . $sDb->quote($start)), $options['limit'])
		);
		if (!$users) {
			return $this->_getNextUserStep();
		}

		XenForo_Db::beginTransaction();

		$next = 0;
		$total = 0;
		foreach ($users AS $user)
		{
			$next = $user['user_id'];

			$imported = $this->_importOrMergeUser($user, $options);
			if ($imported) {
				$total++;
			}
		}

		XenForo_Db::commit();

		$this->_session->incrementStepImportTotal($total);

		return array($next, $options, $this->_getProgressOutput($next, $options['max']));
	}

	public function stepUsersMerge($start, array $options)
	{
		$sDb = $this->_sourceDb;

		$manual = $this->_session->getExtraData('userMerge');

		if ($manual) {
			$merge = $sDb->fetchAll($this->_getSelectUserSql('user.user_id IN (' . $sDb->quote(array_keys($manual)) . ')'));

			$resolve = $this->_controller->getInput()->filterSingle('resolve', XenForo_Input::ARRAY_SIMPLE);
			if ($resolve && !empty($options['shownForm'])) {
				$this->_session->unsetExtraData('userMerge');
				$this->_resolveUserConflicts($merge, $resolve);
			}
			else
			{
				// prevents infinite loop if redirected back to step
				$options['shownForm'] = true;
				$this->_session->setStepInfo(0, $options);

				$users = array();
				foreach ($merge AS $user)
				{
					$users[$user['user_id']] = array(
						'username' => $user['user_login'],
						'email' => $user['user_mail'],
						'register_date' => $user['user_date_register'],
						'conflict' => $manual[$user['user_id']]
					);
				}

				return $this->_controller->responseView(
					'XenForo_ViewAdmin_Import_MergeUsers', 'import_merge_users', array('users' => $users)
				);
			}
		}

		return $this->_getNextUserStep();
	}

	public function stepUsersFailed($start, array $options)
	{
		$sDb = $this->_sourceDb;

		$manual = $this->_session->getExtraData('userFailed');

		if ($manual) {
			$users = $this->_sourceDb->fetchAll($this->_getSelectUserSql('user.user_id IN (' . $sDb->quote(array_keys($manual)) . ')'));

			$resolve = $this->_controller->getInput()->filterSingle('resolve', XenForo_Input::ARRAY_SIMPLE);
			if ($resolve && !empty($options['shownForm'])) {
				$this->_session->unsetExtraData('userFailed');
				$this->_resolveUserConflicts($users, $resolve);
			}
			else
			{
				// prevents infinite loop if redirected back to step
				$options['shownForm'] = true;
				$this->_session->setStepInfo(0, $options);

				$failedUsers = array();
				foreach ($users AS $user)
				{
					$failedUsers[$user['user_id']] = array(
						'username' => $user['user_login'],
						'email' => $user['user_mail'],
						'register_date' => $user['user_date_register'],
						'failure' => $manual[$user['user_id']]
					);
				}

				return $this->_controller->responseView(
					'XenForo_ViewAdmin_Import_FailedUsers', 'import_failed_users', array('users' => $failedUsers)
				);
			}
		}

		return $this->_getNextUserStep();
	}

	protected function _resolveUserConflicts(array $users, array $resolve)
	{
		/* @var $model XenForo_Model_Import */
		$model = $this->_importModel;

		$total = 0;

		XenForo_Db::beginTransaction();

		foreach ($users AS $user)
		{
			if (empty($resolve[$user['user_id']])) {
				continue;
			}

			$info = $resolve[$user['user_id']];

			if (empty($info['action']) || $info['action'] == 'change') {
				if (isset($info['email'])) {
					$user['user_mail'] = $info['email'];
				}
				if (isset($info['username'])) {
					$user['user_login'] = $info['username'];
				}

				$imported = $this->_importOrMergeUser($user);
				if ($imported) {
					$total++;
				}
			}
			else if ($info['action'] == 'merge') {
				$im = $this->_importModel;

				if ($match = $im->getUserIdByEmail($user['user_mail'])) {
					$this->_mergeUser($user, $match);
				}
				else if ($match = $im->getUserIdByUserName($user['user_login'])) {
					$this->_mergeUser($user, $match);
				}

				$total++;
			}
		}

		XenForo_Db::commit();

		$this->_session->incrementStepImportTotal($total, 'users');
	}

	protected function _getNextUserStep()
	{
		if ($this->_session->getExtraData('userMerge')) {
			return 'usersMerge';
		}

		if ($this->_session->getExtraData('userFailed')) {
			return 'usersFailed';
		}

		return true;
	}

	protected function _importOrMergeUser(array $user, array $options = array())
	{
		$im = $this->_importModel;

		if ($user['user_mail'] && $emailMatch = $im->getUserIdByEmail($user['user_mail'])) {
			if (!empty($options['mergeEmail'])) {
				return $this->_mergeUser($user, $emailMatch);
			}
			else
			{
				if ($im->getUserIdByUserName($user['user_login'])) {
					$this->_session->setExtraData('userMerge', $user['user_id'], 'both');
				}
				else
				{
					$this->_session->setExtraData('userMerge', $user['user_id'], 'email');
				}
				return false;
			}
		}

		$name = utf8_substr(rtrim($user['user_login']), 0, 50);

		if ($nameMatch = $im->getUserIdByUserName($name)) {
			if (!empty($options['mergeName'])) {
				return $this->_mergeUser($user, $nameMatch);
			}
			else
			{
				$this->_session->setExtraData('userMerge', $user['user_id'], 'name');
				return false;
			}
		}

		return $this->_importUser($user, $options);
	}

	protected function _importUser(array $user, array $options)
	{
		$salt = XenForo_Application::generateRandomString(30);
		$hash = md5($user['user_password'] . $salt);

		$import = array(
			'username' => $user['user_login'],
			'email' => $user['user_mail'],
			'user_group_id' => 2,
			'authentication' => array(
				'scheme_class' => 'XenForo_Authentication_vBulletin',
				'data' => array(
					'hash' => $hash,
					'salt' => $salt
				)
			),
			'homepage' => $user['user_profile_site'],
			'register_date' => $user['user_date_register'],
		);


		$importedUserId = $this->_importModel->importUser($user['user_id'], $import, $failedKey);
		if ($importedUserId && $user['user_profile_avatar']) {
			//$imagePath = $this->_config['imagePath'];
			$avatarUrl = $user['user_profile_avatar'];
            $avatarUrl = str_replace('_100x100', '', $avatarUrl);

            $data = XenForo_Helper_Http::getClient($avatarUrl)->request('GET')->getBody();

            $avatarFile = tempnam(XenForo_Helper_File::getTempDir(), 'xf');

            if ($data && $data[0] != '{' && $avatarFile) // ensure it's not a json response
            {


                file_put_contents($avatarFile, $data);

                try
                {
                    //$user = array_merge($user,
                        XenForo_Model::create('XenForo_Model_Avatar')->applyAvatar($importedUserId, $avatarFile);
                    //);
                }
                catch (XenForo_Exception $e) {}
            }

            @unlink($avatarFile);
		}
		else if ($failedKey) {
			$this->_session->setExtraData('userFailed', $user['user_id'], $failedKey);
		}

		return $importedUserId;
	}

	protected function _getSelectUserSql($where)
	{
		return '
			SELECT user.*, UNIX_TIMESTAMP( user_date_register ) AS user_date_register
			FROM ' . $this->_prefix . 'user AS user
			WHERE ' . $where . '
			ORDER BY user.user_id
		';
	}

	protected function _mergeUser(array $user, $targetUserId)
	{
		$this->_db->query('
			UPDATE xf_user SET
				register_date = IF(register_date > ?, ?, register_date)
			WHERE user_id = ?
		', array($user['user_date_register'], $user['user_date_register'], $targetUserId));

		$this->_importModel->logImportData('user', $user['user_id'], $targetUserId);

		return $targetUserId;
	}

	public function stepBlogs($start, array $options)
	{
		$options = array_merge(array(
			'limit' => 1000,
			'max' => false
		), $options);

		$sDb = $this->_sourceDb;
		$prefix = $this->_prefix;

		/* @var $model XenForo_Model_Import */
		$model = $this->_importModel;

		if ($options['max'] === false) {
			$options['max'] = $sDb->fetchOne('
				SELECT MAX(blog_id)
				FROM ' . $prefix . 'blog
			');
		}

		if ($start > 0) {
			// after importing everything, rebuild the full permission cache so forums appear
			XenForo_Model::create('XenForo_Model_Node')->updateNestedSetInfo();
			XenForo_Model::create('XenForo_Model_Permission')->rebuildPermissionCache();
			return true;
		}

		// pull threads from things we actually imported as forums
		$blogs = $sDb->fetchAll($sDb->limit(
			'
				SELECT blog.*, UNIX_TIMESTAMP( blog_date_add ) AS blog_date_add
				FROM ' . $prefix . 'blog AS blog FORCE INDEX (PRIMARY)
				WHERE blog.blog_id >= ' . $sDb->quote($start) . ' AND blog.blog_type != \'personal\'
				ORDER BY blog.blog_id
			', $options['limit']
		));
		if (!$blogs) {
			return true;
		}

		$next = 0;
		$total = 0;
        $blogsRoot = Social_Helper_Node::getTypeRootFromCache('blog');
        if (!isset($blogsRoot['node_id'])) {
            $errors[] = new XenForo_Phrase('blog_root_not_found');
        }

		$containerId = $blogsRoot['node_id'];

		XenForo_Db::beginTransaction();

		foreach ($blogs AS $blog)
		{
			$next = $blog['blog_id'] + 1;

			$import = array(
				'title' => $blog['blog_title'],
				//'owner_user_id' => $model->mapUserId($blog['user_owner_id'], 0),
				//'creation_date' => $blog['blog_date_add'],
				'node_type_id' => 'Forum',
				'parent_node_id' => $containerId,
			);

			$newBlogId = $model->importForum($blog['blog_id'], $import);

			if (!$newBlogId) {
				continue;
			}

			$total++;
		}

		XenForo_Db::commit();

		$this->_session->incrementStepImportTotal($total);

		return array($next, $options, $this->_getProgressOutput($next, $options['max']));
	}

	public function stepTopics($start, array $options)
	{
		$options = array_merge(array(
			'limit' => 1000,
			'max' => false
		), $options);

		$sDb = $this->_sourceDb;
		$prefix = $this->_prefix;

		/* @var $model XenForo_Model_Import */
		$model = $this->_importModel;

		if ($options['max'] === false) {
			$options['max'] = $sDb->fetchOne('
				SELECT MAX(topic_id)
				FROM ' . $prefix . 'topic
			');
		}

		// pull threads from things we actually imported as forums
		$topics = $this->keyRows($sDb->fetchAll($sDb->limit(
			'
				SELECT *, UNIX_TIMESTAMP( topic_date_add ) AS topic_date_add
				FROM ' . $prefix . 'topic AS topic FORCE INDEX (PRIMARY)
				LEFT JOIN ' . $prefix . 'user AS user ON user.user_id = topic.user_id
				INNER JOIN ' . $prefix . 'topic_content AS content ON content.topic_id = topic.topic_id
				WHERE topic.topic_id >= ' . $sDb->quote($start) . '
				ORDER BY topic.topic_id
			', $options['limit']
		)), 'topic_id');

		if (!$topics) {
			return true;
		}

		$next = 0;
		$total = 0;

		XenForo_Db::beginTransaction();

		foreach ($topics AS $topic)
		{
			$next = $topic['topic_id'] + 1;

			$message = Social_Importer_LivestreetHelper_Html::renderFromHtml($topic['topic_text']);

			if (!$message) {
				throw new XenForo_Exception('Empty topic_text in topic ' . $topic['topic_id']);
			}


            $personalBlogsRoot = XenForo_Model::create('XenForo_Model_Forum')->getForumById(XenForo_Application::getOptions()->get('socialPersonalBlogRoot'));
            if (!isset($personalBlogsRoot['node_id'])) {
                $errors[] = new XenForo_Phrase('personal_blog_root_not_found');
            }
			$containerId = $personalBlogsRoot['node_id'];

			$importThread = array(
				'title' => $topic['topic_title'],
				'user_id' => $model->mapUserId($topic['user_id'], 0),
				'username' => $topic['user_login'],
				'node_id' => $model->mapNodeId($topic['blog_id'], $containerId),
				'post_date' => $topic['topic_date_add'],
				'discussion_state' => 'visible',
			);

			$newThreadId = $model->importThread($topic['topic_id'], $importThread);

			if (!$newThreadId) {
				continue;
			}

			$importFirstPost = array(
				'position' => 0,
				'user_id' => $model->mapUserId($topic['user_id'], 0),
				'username' => $topic['user_login'],
				'thread_id' => $newThreadId,
				'post_date' => $topic['topic_date_add'],
				'message' => $message,
				'message_state' => 'visible',
			);

			$newPostId = $model->importPost((-1) * $topic['topic_id'], $importFirstPost);

			$total++;
		}

		$likes = $sDb->fetchAll(
			'
				SELECT *, UNIX_TIMESTAMP( vote_date ) AS vote_date
				FROM ' . $prefix . 'vote AS vote
				WHERE vote.target_id IN (' . $sDb->quote(array_keys($topics)) . ') AND vote.target_type = \'topic\' AND vote.vote_direction = 1
				ORDER BY vote.target_id
			'
		);

		foreach ($likes AS $like)
		{
			$model->importLike(
				'post',
				$model->mapPostId((-1) * $like['target_id']),
				$model->mapUserId($topics[$like['target_id']]['user_id']),
				$model->mapUserId($like['user_voter_id']),
				$like['vote_date']
			);

		}

		XenForo_Db::commit();

		$this->_session->incrementStepImportTotal($total);

		return array($next, $options, $this->_getProgressOutput($next, $options['max']));
	}


	public function stepComments($start, array $options)
	{
		$options = array_merge(array(
			'limit' => 1000,
			'max' => false
		), $options);

		$sDb = $this->_sourceDb;
		$prefix = $this->_prefix;

		/* @var $model XenForo_Model_Import */
		$model = $this->_importModel;

		if ($options['max'] === false) {
			$options['max'] = $sDb->fetchOne('
				SELECT MAX(comment_id)
				FROM ' . $prefix . 'comment
			');
		}

		// pull threads from things we actually imported as forums
		$comments = $this->keyRows($sDb->fetchAll($sDb->limit(
			'
				SELECT  *, UNIX_TIMESTAMP( comment_date ) AS comment_date
				FROM ' . $prefix . 'comment AS comment FORCE INDEX (PRIMARY)
				LEFT JOIN ' . $prefix . 'user AS user ON user.user_id = comment.user_id
				WHERE comment.comment_id >= ' . $sDb->quote($start) . '
				ORDER BY comment.comment_id
			', $options['limit']
		)), 'comment_id');

		if (!$comments) {
			return true;
		}

		$next = 0;
		$total = 0;

		XenForo_Db::beginTransaction();

		foreach ($comments AS $comment)
		{
			$next = $comment['comment_id'] + 1;

			$message = Social_Importer_LivestreetHelper_Html::renderFromHtml($comment['comment_text']);

			if (!$message) {
				throw new XenForo_Exception('Empty comment_text in comment ' . $comment['comment_id']);
			}

			$import = array(
				'position' => 0,
				'user_id' => $model->mapUserId($comment['user_id'], 0),
				'username' => $comment['user_login'],
				'thread_id' => $model->mapThreadId($comment['target_id'], 0),
				'post_date' => $comment['comment_date'],
				'message' => $message,
				'message_state' => 'visible',

				'parent_post_id' => $model->mapPostId($comment['comment_pid'], 0),
			);

			$newPostId = $model->importPost($comment['comment_id'], $import);

			if (!$newPostId) {
				continue;
			}

			$total++;
		}

		$likes = $sDb->fetchAll(
			'
				SELECT *, UNIX_TIMESTAMP( vote_date ) AS vote_date
				FROM ' . $prefix . 'vote AS vote
				WHERE vote.target_id IN (' . $sDb->quote(array_keys($comments)) . ') AND vote.target_type = \'comment\' AND vote.vote_direction = 1
				ORDER BY vote.target_id
			'
		);

		foreach ($likes AS $like)
		{
			$model->importLike(
				'post',
				$model->mapPostId($like['target_id']),
				$model->mapUserId($comments[$like['target_id']]['user_id']),
				$model->mapUserId($like['user_voter_id']),
				$like['vote_date']
			);
		}

		XenForo_Db::commit();

		$this->_session->incrementStepImportTotal($total);

		return array($next, $options, $this->_getProgressOutput($next, $options['max']));
	}

	public function keyRows(array $rows, $key)
	{
		$rowsKeyed = array();
		foreach ($rows as $row) {
			$rowsKeyed[$row[$key]] = $row;
		}
		return $rowsKeyed;
	}
}