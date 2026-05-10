<?php

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity instanceof ElggEntity) {
	register_error(elgg_echo('actions:feature:item_not_found'));
	forward(REFERRER);
}

if (!actions_feature_can_feature($entity)) {
	register_error(elgg_echo('actions:feature:permission_denied'));
	forward(REFERRER);
}

// determine what name to show on success
$display_name = $entity->getDisplayName();
if (!$display_name) {
	$display_name = elgg_echo('actions:feature:item');
}

if (empty($entity->featured) && $entity->featured_group != 'yes') {
	register_error(elgg_echo('actions:unfeature:error', [$display_name]));
	forward(REFERRER);
}

$entity->featured = false;
if ($entity instanceof ElggGroup) {
	// compatibility with the group plugin
	$entity->featured_group = 'no';
}

elgg_trigger_event('unfeatured', $entity->getType(), $entity);

system_message(elgg_echo('actions:unfeature:success', [$display_name]));
forward(REFERRER);
