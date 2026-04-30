<?php

return [
	'bootstrap' => \ActionsFeature\Bootstrap::class,

	'plugin' => [
		'version' => '5.0.0',
	],

	'actions' => [
		'feature' => [],
		'unfeature' => [],
	],

	'events' => [
		'register' => [
			'menu:entity' => [
				\ActionsFeature\Menus::class . '::entityMenu' => [],
			],
		],
		'feature' => [
			'group' => [
				'Elgg\Values::getTrue' => [],
			],
		],
	],
];
