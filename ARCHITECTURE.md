# actions_feature — Architecture (Elgg 5.x)

## Overview

`actions_feature` is a small Elgg 5.x plugin that lets admins mark entities
as "featured". It provides permission events, entity-menu actions, and a DB
toggle pattern built entirely on Elgg's metadata API.

## Entry Points

| File | Purpose |
|------|---------|
| `elgg-plugin.php` | Plugin manifest: bootstrap class, action registration, hook declarations |
| `classes/ActionsFeature/Bootstrap.php` | Elgg 4.x bootstrap (implements `\Elgg\PluginBootstrapInterface`) |

## Classes

| Class | Responsibility |
|-------|---------------|
| `ActionsFeature\Bootstrap` | Registers no extra services; all wiring is declarative in `elgg-plugin.php` |
| `ActionsFeature\Permissions` | `canFeature(entity, user?)` — checks allowed type + admin status via events |
| `ActionsFeature\Menus` | `entityMenu(Event)` — injects Feature/Unfeature items into `register, menu:entity` |

## Events Registered

| Event name | Type | Handler | Purpose |
|-----------|------|---------|---------|
| `register` | `menu:entity` | `Menus::entityMenu` | Add Feature/Unfeature to entity title dropdown |
| `feature` | `group` | `Elgg\Values::getTrue` | Opt groups into the feature system |

## Actions

| Action | File | Effect |
|--------|------|--------|
| `feature` | `actions/feature.php` | Sets `entity->featured = 1`, `entity->featured_group = 'yes'` |
| `unfeature` | `actions/unfeature.php` | Sets `entity->featured = 0`, `entity->featured_group = 'no'` |

Both actions require the CSRF token and redirect back with a system message.

## Extension Points

Other plugins can register additional entity types into the feature system:

```php
// In elgg-plugin.php:
'events' => [
    'feature' => [
        'object' => ['Elgg\Values::getTrue' => []],
    ],
],
```

Admins can also override the permission check per entity type via:

```php
'events' => [
    'permissions_check:feature' => [
        'group' => ['MyPlugin\Permissions::customCheck' => []],
    ],
],
```

## Data Model

Feature state is stored as metadata on the entity:

| Metadata key | Values | Set by |
|-------------|--------|--------|
| `featured` | `1` / `0` | `feature.php` / `unfeature.php` |
| `featured_group` | `'yes'` / `'no'` | same |

`featured_group` is a legacy key retained for backwards compatibility with
views that check it via `$entity->featured_group == 'yes'`.

## Rendering

Menu items appear in the title dropdown (the ellipsis `⋮` button in the page
title area). The `item_class` on each `ElggMenuItem` controls visibility:
the active item has no class, the inactive item has `'hidden'`.

## Migration Notes (4.x → 5.x)

- `elgg-plugin.php` `'hooks'` key replaced by `'events'` (Elgg 5.x unified event system)
- `Menus::entityMenu(Hook $hook)` → `entityMenu(Event $event)` — type hint updated
- `elgg_trigger_plugin_hook()` → `elgg_trigger_event_results()` in Permissions
- `elgg_get_session()->setLoggedInUser()` → `_elgg_services()->session_manager->setLoggedInUser()` (in tests and install script)
- Docker stack: PHP 8.2, MySQL 8.0, Elgg ~5.1.0
- No data migration required — feature state stored as plain metadata

## Migration Notes (3.x → 4.x)

- `manifest.xml` removed; metadata now lives in `composer.json` + `elgg-plugin.php`
- `start.php` removed; bootstrap moved to `Bootstrap::class`
- `elgg-plugin.php` now uses declarative hook registration (no closures)
- `isAllowedType()` fixed: uses `$entity->getType()` only (not `type:subtype`),
  because `ElggGroup::getSubtype()` returns `'group'` in Elgg 4.x, which would
  produce `'group:group'` — a hook name that never matches handlers registered
  for `'feature', 'group'`
- `composer.json` updated with `elgg/elgg: ^4.0` and `composer/installers: ^2.0`
