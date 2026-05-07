<?php
/**
 * REST API integration.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Exposes CFL meta to REST responses.
 */
final class Rest_API {
	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action('rest_api_init', [$this, 'register_fields']);
	}

	/**
	 * Register REST fields for public post types.
	 */
	public function register_fields(): void {
		$post_types = get_post_types(['show_in_rest' => true], 'names');

		foreach ($post_types as $post_type) {
			register_rest_field(
				$post_type,
				'cfl_fields',
				[
					'get_callback' => [$this, 'get_fields'],
					'schema'       => [
						'description' => esc_html__('Custom Fields Lite values.', 'custom-fields-lite'),
						'type'        => 'object',
						'context'     => ['view', 'edit'],
					],
				]
			);
		}
	}

	/**
	 * Get REST field values.
	 *
	 * @param array<string, mixed> $object REST object.
	 *
	 * @return array<string, mixed>
	 */
	public function get_fields(array $object): array {
		$post = get_post((int) ($object['id'] ?? 0));

		if (! $post) {
			return [];
		}

		$values = [];
		foreach ((new Field_Group_Repository())->for_post($post) as $group) {
			foreach ((array) ($group['fields'] ?? []) as $field) {
				$name = (string) ($field['name'] ?? '');
				if ('' !== $name) {
					$values[$name] = get_post_meta($post->ID, $name, true);
				}
			}
		}

		return $values;
	}
}
