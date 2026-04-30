<?php

return [
	'bootstrap' => \ActionsFeature\Bootstrap::class,

	'plugin' => [
		'version' => '4.0.0',
	],

	'actions' => [
		'feature' => [],
		'unfeature' => [],
	],

	'hooks' => [
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
