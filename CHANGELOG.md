# Changelog

## [6.0.0] (2026-05-09) — Elgg 6.x migration

### Breaking Changes

- Minimum Elgg version is now **6.0**. Elgg 5.x is no longer supported.
- PHP minimum is now **8.1**.
- AMD/RequireJS removed — `feature.js` converted to ES module.
- `elgg_require_js('feature')` → `elgg_import_esm('feature')`.

### Changes

- Bumped `elgg/elgg` to `~6.1.0`, added `ext-intl`
- Converted `views/default/feature.js` from AMD to ES module
- Updated Docker stack to Elgg 6.x (PHPUnit ^10.5)

---

## [Unreleased] — Elgg 5.x migration

### Breaking Changes

- Minimum Elgg version is now **5.0**. Elgg 4.x is no longer supported.
- PHP minimum is now **8.2**.
- `elgg-plugin.php` `'hooks'` key replaced by `'events'` key (Elgg 5.x unified event system).
- Extension plugins must use `'events'` key instead of `'hooks'` when declaring `feature`/`permissions_check:feature` handlers.

### Changed

- `composer.json`: `php >=8.2`, `elgg/elgg ~5.1.0`.
- `ActionsFeature\Menus::entityMenu()`: parameter type hint changed from `\Elgg\Hook` to `\Elgg\Event`.
- `ActionsFeature\Permissions`: `elgg_trigger_plugin_hook()` replaced by `elgg_trigger_event_results()`.
- Docker test stack: PHP 7.4→8.2, MySQL 5.7→8.0, `elgg-composer.json` targets `~5.1.0`.
- PHPUnit tests adapted: `session_manager` service, `elgg_register_event_handler`, `\Elgg\Event` mock.

## [4.0.0] — Elgg 4.x migration

### Breaking Changes

- Minimum Elgg version is now **4.0**. Elgg 3.x is no longer supported.
- `manifest.xml` has been removed. Plugin metadata is now declared via
  `composer.json` (`name`, `description`, `type: elgg-plugin`) and
  `elgg-plugin.php` (`plugin.name`, `plugin.description`).
- `start.php` has been removed. Bootstrapping is now handled by
  `ActionsFeature\Bootstrap` via the `bootstrap` key in `elgg-plugin.php`.

### Fixed

- `Permissions::isAllowedType()` now uses `$entity->getType()` as the hook
  type instead of `$entity->getType() . ':' . $entity->getSubtype()`.
  In Elgg 4.x, `ElggGroup::getSubtype()` returns `'group'`, which caused the
  hook type to be `'group:group'` — a string that never matched handlers
  registered for `'feature', 'group'`. Feature/Unfeature menu items on groups
  were silently hidden as a result.

### Changed

- `composer.json` updated: requires `elgg/elgg: ^4.0` and
  `composer/installers: ^2.0`; `extra.elgg-plugin-id` added.
- Hook registration moved from `start.php` to the declarative `hooks` array
  in `elgg-plugin.php` (Elgg 4.x preferred pattern).
- Code style: tabs → 4-space indentation, PSR-12 compliance.

### Added

- Per-plugin Docker test stack (`docker/docker-compose.yml`,
  `docker/elgg-install.sh`, `docker/Dockerfile`) for isolated Elgg 4.x
  activation and test runs.
- PHPUnit integration test suite (`tests/phpunit/`) covering permissions,
  menus, and feature/unfeature action logic.
- Playwright end-to-end test suite (`tests/playwright/`) covering the
  feature-toggle flow, non-admin access control, and direct action URL
  protection.
