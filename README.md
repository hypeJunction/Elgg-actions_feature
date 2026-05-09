Feature Action for Elgg
=======================
![Elgg 4.0](https://img.shields.io/badge/Elgg-4.0-orange.svg?style=flat-square)

## Features

* Standardized API for featuring items

## Usage

 * Register your type

```php
elgg_register_plugin_hook_handler('feature', 'object:my_type', 'Elgg\Values::getTrue');
```

 * Manage permissions

By default, only admins can feature/unfeature items. If you need to change that behaviour,
use `'permissions_check:feature',$entity_type` hook.

 * Notifications/River items

If you need to notify the owner, or create a river item, listen to `'featured',$entity_type` and
`'unfeatured',$entity_type` events.
