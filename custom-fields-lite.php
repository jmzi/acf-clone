<?php
/**
 * Plugin Name: Custom Fields Lite
 * Plugin URI: https://example.com/custom-fields-lite
 * Description: Lightweight custom fields and repeater fields for WordPress.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: Jimmy
 * Text Domain: custom-fields-lite
 * Domain Path: /languages
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('CFL_VERSION', '1.0.0');
define('CFL_FILE', __FILE__);
define('CFL_PATH', plugin_dir_path(__FILE__));
define('CFL_URL', plugin_dir_url(__FILE__));

spl_autoload_register(
	static function (string $class): void {
		$prefix = 'CustomFieldsLite\\';

		if (0 !== strpos($class, $prefix)) {
			return;
		}

		$relative = substr($class, strlen($prefix));
		$relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);
		$file     = CFL_PATH . strtolower(str_replace('_', '-', $relative)) . '.php';

		if (is_readable($file)) {
			require_once $file;
		}
	}
);

require_once CFL_PATH . 'includes/template-functions.php';

register_activation_hook(CFL_FILE, ['CustomFieldsLite\\Includes\\Activator', 'activate']);
register_deactivation_hook(CFL_FILE, ['CustomFieldsLite\\Includes\\Deactivator', 'deactivate']);

add_action(
	'plugins_loaded',
	static function (): void {
		CustomFieldsLite\Includes\Plugin::instance()->init();
	}
);
