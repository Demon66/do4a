<?php

$config['widgets']['Social_Widget_LatestThreads'] = array
(
	array
	(
		'controller' => 'Social_ControllerPublic_News',
		'action' => 'Index',
		'position' => 5,
        //'limit' => 20,
	),
	array
	(
		'controller' => 'Thread',
		'action' => 'Index',
		//'section' => 'blogs',
		'position' => 15,
	),
);

$config['widgets']['Social_Widget_LatestEntries'] = array
(
    array
    (
        'controller' => 'Social_ControllerPublic_News',
        'action' => 'Index',
        'position' => 4,
        //'limit' => 20,
    ),
	array
	(
		'controller' => 'Forum',
		'action' => 'Index',
		'section' => 'blogs',
		'position' => 5,
		'limit' => 5,
	),
	array
	(
		'controller' => 'Category',
		'action' => 'Index',
		'section' => 'blogs',
		'position' => 5,
        'limit' => 12,
	),
	array
	(
		'controller' => 'Thread',
		'action' => 'Index',
		'section' => 'blogs',
		'position' => 5,
		'currentNode' => 1,
	),
    array
   	(
   		'controller' => 'Thread',
   		'action' => 'Index',
   		'section' => 'forums',
   		'position' => 5,
   	),
);

$config['widgets']['Social_Widget_OnlineMembers'] = array
(
	array
	(
		'controller' => 'Social_ControllerPublic_News',
		'action' => 'Index',
		'position' => 9,
	),
);

$config['widgets']['Social_Widget_OnlineStaff'] = array
(
);

$config['widgets']['Social_Widget_ForumStats'] = array
(
	array
	(
		'controller' => 'Social_ControllerPublic_News',
		'action' => 'Index',
		'position' => 10
	),
	array
	(
		'controller' => 'Forum',
		'action' => 'Index',
		'section' => 'blogs',
		'position' => 15,
	),
	array
	(
		'controller' => 'Thread',
		'action' => 'Index',
		'section' => 'blogs',
		'position' => 15,
	),
);

$config['widgets']['Social_Widget_SharePage'] = array
(
	array
	(
		'controller' => 'Social_ControllerPublic_News',
		'action' => 'Index',
		'position' => 200
	),
	array
	(
		'controller' => 'Forum',
		'action' => 'Index',
		'section' => 'blogs',
		'position' => 200,
	),
	array
	(
		'controller' => 'Thread',
		'action' => 'Index',
		'section' => 'blogs',
		'position' => 200,
	),
);