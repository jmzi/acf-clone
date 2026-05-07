<?php
/**
 * Main plugin loader.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

use CustomFieldsLite\Admin\Admin;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Coordinates plugin services.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Field registry.
	 *
	 * @var Field_Registry
	 */
	private Field_Registry $field_registry;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): self {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->field_registry = new Field_Registry();
	}

	/**
	 * Initialize hooks.
	 */
	public function init(): void {
		load_plugin_textdomain('custom-fields-lite', false, dirname(plugin_basename(CFL_FILE)) . '/languages');

		(new Post_Type())->init();
		(new Assets())->init();
		(new Ajax($this->field_registry))->init();
		(new Meta_Box($this->field_registry))->init();
		(new Rest_API())->init();

		if (is_admin()) {
			(new Admin($this->field_registry))->init();
		}
	}

	/**
	 * Get the field registry.
	 */
	public function fields(): Field_Registry {
		return $this->field_registry;
	}
}
