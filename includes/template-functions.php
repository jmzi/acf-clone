<?php
/**
 * Frontend helper functions.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

use CustomFieldsLite\Includes\Loop;

if (! defined('ABSPATH')) {
	exit;
}

if (! function_exists('cfl_get_field')) {
	/**
	 * Get a custom field.
	 *
	 * @param string   $field_name Field name.
	 * @param int|null $post_id    Optional post ID.
	 *
	 * @return mixed
	 */
	function cfl_get_field(string $field_name, ?int $post_id = null): mixed {
		$post_id = $post_id ?: get_the_ID();

		if (! $post_id) {
			return null;
		}

		return get_post_meta($post_id, $field_name, true);
	}
}

if (! function_exists('cfl_the_field')) {
	/**
	 * Echo an escaped custom field.
	 */
	function cfl_the_field(string $field_name, ?int $post_id = null): void {
		$value = cfl_get_field($field_name, $post_id);
		echo wp_kses_post(is_scalar($value) ? (string) $value : '');
	}
}

if (! function_exists('cfl_have_rows')) {
	/**
	 * Whether a repeater has another row.
	 */
	function cfl_have_rows(string $field_name, ?int $post_id = null): bool {
		return Loop::instance()->have_rows($field_name, $post_id ?: get_the_ID());
	}
}

if (! function_exists('cfl_the_row')) {
	/**
	 * Advance repeater pointer.
	 */
	function cfl_the_row(): void {
		Loop::instance()->the_row();
	}
}

if (! function_exists('cfl_get_sub_field')) {
	/**
	 * Get current repeater row sub field.
	 *
	 * @return mixed
	 */
	function cfl_get_sub_field(string $field_name): mixed {
		return Loop::instance()->get_sub_field($field_name);
	}
}

if (! function_exists('cfl_the_sub_field')) {
	/**
	 * Echo current repeater row sub field.
	 */
	function cfl_the_sub_field(string $field_name): void {
		$value = cfl_get_sub_field($field_name);
		echo wp_kses_post(is_scalar($value) ? (string) $value : '');
	}
}
