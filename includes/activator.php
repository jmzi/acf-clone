<?php
/**
 * Activation logic.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Handles plugin activation.
 */
final class Activator {
	/**
	 * Activate plugin.
	 */
	public static function activate(): void {
		(new Post_Type())->register();
		flush_rewrite_rules();
	}
}
