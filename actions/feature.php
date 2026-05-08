<?php

use ActionsFeature\Permissions;

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof ElggEntity) {
	return elgg_error_response(elgg_echo('actions:feature:item_not_found'));
}

if (!Permissions::canFeature($entity)) {
	return elgg_error_response(elgg_echo('actions:feature:permission_denied'));
}

// determine what name to show on success
$display_name = $entity->getDisplayName();
if (!$display_name) {
	$display_name = elgg_echo('actions:feature:item');
}

if ($entity->featured) {
	return elgg_error_response(elgg_echo('actions:feature:error', [$display_name]));
}

$entity->featured = true;
if ($entity instanceof ElggGroup) {
	// compatibility with the group plugin
	$entity->featured_group = 'yes';
}

elgg_trigger_event('featured', $entity->getType(), $entity);

return elgg_ok_response('', elgg_echo('actions:feature:success', [$display_name]));
