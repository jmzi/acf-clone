<?php
/**
 * Deactivation logic.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Handles plugin deactivation.
 */
final class Deactivator {
	/**
	 * Deactivate plugin.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
