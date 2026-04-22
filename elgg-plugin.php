<?php

return [
	'bootstrap' => \ActionsFeature\Bootstrap::class,

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
