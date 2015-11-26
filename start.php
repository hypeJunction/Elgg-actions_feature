<?php

/**
 * Feature Action
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */

elgg_register_event_handler('init', 'system', 'actions_feature_init');

/**
 * Initialize the plugin
 * @return void
 */
function actions_feature_init() {

	elgg_register_action('feature', __DIR__ . '/actions/feature.php');
	elgg_register_action('unfeature', __DIR__ . '/actions/unfeature.php');

	elgg_register_plugin_hook_handler('register', 'menu:entity', 'actions_feature_entity_menu_setup');

	elgg_extend_view('js/elgg', 'feature.js');

	elgg_register_plugin_hook_handler('feature', 'group', 'Elgg\Values::getTrue');
}

/**
 * Check if entity type is registered for feature/unfeature
 *
 * @param ElggEntity $entity Entity to be featured
 * @return bool
 */
function actions_feature_is_allowed_type(ElggEntity $entity) {
	$type = $entity->getType();
	$subtype = $entity->getSubtype();
	$hook_type = implode(':', array_filter([$type, $subtype]));
	return elgg_trigger_plugin_hook('feature', $hook_type, ['entity' => $entity], false);
}

/**
 * Check if user can feature the entity
 *
 * @param ElggEntity $entity Entity to be featured
 * @param ElggUser   $user   User to perform the action
 * @return bool
 */
function actions_feature_can_feature(ElggEntity $entity, ElggUser $user = null) {

	if (!isset($user)) {
		$user = elgg_get_logged_in_user_entity();
	}

	if (!$entity instanceof ElggEntity || !$user instanceof ElggUser) {
		return false;
	}

	if (!actions_feature_is_allowed_type($entity)) {
		return false;
	}

	$default = $user->isAdmin();
	$params = [
		'entity' => $entity,
		'user' => $user,
	];

	return elgg_trigger_plugin_hook('permissions_check:feature', $entity->getType(), $params, $default);
}

/**
 * Add feature/unfeature menu items
 *
 * @param string         $hook   "register"
 * @param string         $type   "menu:entity"
 * @param ElggMenuItem[] $return Menu
 * @param array          $params Hook params
 * @return ElggMenuitem[]
 */
function actions_feature_entity_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	if (!$entity instanceof ElggEntity || !actions_feature_can_feature($entity)) {
		return;
	}

	$featured = $entity->featured || $entity->featured_group == 'yes';
	$return[] = ElggMenuItem::factory([
				'name' => 'feature',
				'text' => elgg_echo("actions:feature"),
				'href' => "action/feature?guid=$entity->guid",
				'is_action' => true,
				'priority' => 300,
				'item_class' => $featured ? 'hidden' : '',
	]);

	$return[] = ElggMenuItem::factory([
				'name' => 'unfeature',
				'text' => elgg_echo("actions:unfeature"),
				'href' => "action/unfeature?guid=$entity->guid",
				'is_action' => true,
				'priority' => 300,
				'item_class' => $featured ? '' : 'hidden',
	]);

	return $return;
}
