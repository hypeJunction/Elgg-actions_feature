<?php

namespace ActionsFeature;

class Permissions {

	/**
	 * Check if entity type is registered for feature/unfeature
	 *
	 * @param \ElggEntity $entity Entity to be featured
	 * @return bool
	 */
	public static function isAllowedType(\ElggEntity $entity): bool {
		$type = $entity->getType();
		$subtype = $entity->getSubtype();
		$hook_type = implode(':', array_filter([$type, $subtype]));

		return (bool) \elgg_trigger_plugin_hook('feature', $hook_type, ['entity' => $entity], false);
	}

	/**
	 * Check if user can feature the entity
	 *
	 * @param \ElggEntity $entity Entity to be featured
	 * @param \ElggUser|null $user User to perform the action
	 * @return bool
	 */
	public static function canFeature(\ElggEntity $entity, \ElggUser $user = null): bool {
		if (!isset($user)) {
			$user = \elgg_get_logged_in_user_entity();
		}

		if (!$entity instanceof \ElggEntity || !$user instanceof \ElggUser) {
			return false;
		}

		if (!self::isAllowedType($entity)) {
			return false;
		}

		$default = $user->isAdmin();
		$params = [
			'entity' => $entity,
			'user' => $user,
		];

		return (bool) \elgg_trigger_plugin_hook('permissions_check:feature', $entity->getType(), $params, $default);
	}
}
