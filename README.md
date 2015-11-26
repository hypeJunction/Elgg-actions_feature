Albums for Elgg
===============
![Elgg 1.11](https://img.shields.io/badge/Elgg-1.11.x-orange.svg?style=flat-square)
![Elgg 1.12](https://img.shields.io/badge/Elgg-1.12.x-orange.svg?style=flat-square)

## Features

* Standardized API for featuring items

## Usage

1. Register your type

```php
elgg_register_plugin_hook_handler('feature', 'object:my_type', 'Elgg\Values::getTrue');
```

2. Manage permissions

By default, only admins can feature/unfeature items. If you need to change that behaviour,
use `'permissions_check:feature',$entity_type` hook.

3. Notifications/River items

If you need to notify the owner, or create a river item, listen to `'featured',$entity_type` and
`'unfeatured',$entity_type` events.