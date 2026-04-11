<?php

return [
	'bootstrap' => \ActionsFeature\Bootstrap::class,

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
