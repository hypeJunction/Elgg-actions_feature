# Changelog

## [Unreleased] — Elgg 4.x migration

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
