<?php

namespace ActionsFeature;

use Elgg\Event;

class Menus
{
    /**
     * Add feature/unfeature menu items
     *
     * @param \Elgg\Event $event 'register', 'menu:entity'
     * @return \ElggMenuItem[]|void
     */
    public static function entityMenu(Event $event)
    {
        $entity = $event->getEntityParam();

        if (!$entity instanceof \ElggEntity || !Permissions::canFeature($entity)) {
            return;
        }

        $return = $event->getValue();

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
