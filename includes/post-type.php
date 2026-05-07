<?php
/**
 * Field group post type.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Registers the field group post type.
 */
final class Post_Type {
	public const POST_TYPE = 'cfl_field_group';

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action('init', [$this, 'register']);
	}

	/**
	 * Register custom post type.
	 */
	public function register(): void {
		register_post_type(
			self::POST_TYPE,
			[
				'labels'              => [
					'name'               => esc_html__('Field Groups', 'custom-fields-lite'),
					'singular_name'      => esc_html__('Field Group', 'custom-fields-lite'),
					'add_new_item'       => esc_html__('Add New Field Group', 'custom-fields-lite'),
					'edit_item'          => esc_html__('Edit Field Group', 'custom-fields-lite'),
					'new_item'           => esc_html__('New Field Group', 'custom-fields-lite'),
					'view_item'          => esc_html__('View Field Group', 'custom-fields-lite'),
					'search_items'       => esc_html__('Search Field Groups', 'custom-fields-lite'),
					'not_found'          => esc_html__('No field groups found.', 'custom-fields-lite'),
					'menu_name'          => esc_html__('Custom Fields Lite', 'custom-fields-lite'),
				],
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-feedback',
				'supports'            => ['title'],
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'query_var'           => false,
				'rewrite'             => false,
			]
		);
	}
}
