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

if (empty($entity->featured) && $entity->featured_group != 'yes') {
    return elgg_error_response(elgg_echo('actions:unfeature:error', [$display_name]));
}

$entity->featured = false;
if ($entity instanceof ElggGroup) {
    // compatibility with the group plugin
    $entity->featured_group = 'no';
}

elgg_trigger_event('unfeatured', $entity->getType(), $entity);

return elgg_ok_response('', elgg_echo('actions:unfeature:success', [$display_name]));
