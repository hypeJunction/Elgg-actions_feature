<?php

namespace ActionsFeature;

use Elgg\Hook;

/**
 * Menus class.
 */
class Menus {

	/**
	 * Add feature/unfeature menu items
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:entity'
	 * @return \ElggMenuItem[]|void
	 */
	public static function entityMenu(Hook $hook) {
		$entity = $hook->getEntityParam();

		if (!$entity instanceof \ElggEntity || !Permissions::canFeature($entity)) {
			return;
		}

		$return = $hook->getValue();

		$featured = $entity->featured || $entity->featured_group == 'yes';

		$return[] = \ElggMenuItem::factory([
			'name' => 'feature',
			'text' => \elgg_echo('actions:feature'),
			'href' => \elgg_generate_action_url('feature', [
				'guid' => $entity->guid,
			]),
			'priority' => 300,
			'item_class' => $featured ? 'hidden' : '',
		]);

		$return[] = \ElggMenuItem::factory([
			'name' => 'unfeature',
			'text' => \elgg_echo('actions:unfeature'),
			'href' => \elgg_generate_action_url('unfeature', [
				'guid' => $entity->guid,
			]),
			'priority' => 300,
			'item_class' => $featured ? '' : 'hidden',
		]);

		return $return;
	}
}
