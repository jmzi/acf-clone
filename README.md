# Custom Fields Lite

Custom Fields Lite is a lightweight WordPress plugin that provides a focused custom-field system inspired by the core editing workflow of ACF. It intentionally includes only essential field groups, common field types, and the Repeater field.

## What It Includes

- Field group custom post type in WP Admin
- Location rules for post type, page, and template
- Text, Textarea, Number, Email, URL, Select, Checkbox, Radio, True/False, Image, File, WYSIWYG, and Repeater fields
- Repeater rows with add, remove, collapse, sorting, unlimited rows, sub-fields, nested repeaters, and AJAX-gated duplication
- Native `post_meta` storage
- Gutenberg and Classic Editor meta boxes
- Frontend helper functions similar to ACF
- JSON field group export/import
- Duplicate field group action
- Simple REST API exposure under `cfl_fields`

## Installation

1. Place the `custom-fields-lite` directory in `wp-content/plugins/`.
2. Activate **Custom Fields Lite** in WordPress Admin.
3. Go to **Custom Fields Lite > Add New Field Group**.
4. Add fields and location rules, then publish the field group.

## Architecture

- `custom-fields-lite.php`: plugin header, constants, autoloader, lifecycle hooks.
- `includes/`: core services for loading, assets, field registry, location matching, post meta saving, REST output, AJAX, and frontend loops.
- `admin/`: field group editor UI, field group save logic, duplicate action, import/export tools.
- `fields/`: one class per field type, each responsible for rendering and sanitizing its own value.
- `assets/`: admin JavaScript and CSS.
- `templates/`: example theme usage.

## Field Storage

Field definitions are stored on the field group post type:

- `_cfl_fields`: normalized field configuration array.
- `_cfl_location`: normalized location rule array.

Field values are stored directly as native post meta using the field name as the meta key. Repeater values are saved as sanitized arrays, allowing WordPress to serialize them naturally in `post_meta`.

## Frontend Helpers

```php
$value = cfl_get_field('headline');
cfl_the_field('headline');
```

Repeater example:

```php
if (cfl_have_rows('features')) {
	while (cfl_have_rows('features')) {
		cfl_the_row();
		echo '<h3>' . esc_html(cfl_get_sub_field('title')) . '</h3>';
		cfl_the_sub_field('description');
	}
}
```

## Supported Location Rules

- Post Type equals / does not equal
- Page equals / does not equal
- Template equals / does not equal

Multiple rules are treated as OR conditions. If any rule matches, the field group appears.

## Repeater Notes

Repeaters support nested sub-fields and nested repeater definitions. Rich WYSIWYG sub-fields inside repeaters render as a textarea fallback for reliable dynamic row cloning; top-level WYSIWYG fields use the native WordPress editor.

## Security

- Direct file access is blocked.
- All saves use nonces and capability checks.
- Field values are sanitized by field type.
- Admin output is escaped.
- AJAX duplication verifies nonce and capability.

## Performance

- Assets load only on field group screens and post edit screens.
- Field groups are queried once per request and cached in memory.
- Values use native post meta without custom tables or autoloaded options.
- No external dependencies, telemetry, licensing, or cloud services.

## Intentional Omissions

This plugin does not try to clone all of ACF. It omits flexible content, options pages, relationship fields, block registration, complex conditional logic, field validation UI, bidirectional relationships, local JSON sync, and premium-style settings.

## Developer Extension Points

Register more field handlers by extending `CustomFieldsLite\Includes\Abstract_Field` and adding the handler to the registry from a plugin hook. The current architecture keeps field rendering and sanitization isolated so new fields can be added without rewriting storage or location matching.
