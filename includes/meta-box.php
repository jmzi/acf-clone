<?php
/**
 * Content editor meta boxes.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

use WP_Post;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Renders and saves custom field values.
 */
final class Meta_Box {
	private Field_Registry $registry;

	private Field_Group_Repository $groups;

	public function __construct(Field_Registry $registry) {
		$this->registry = $registry;
		$this->groups   = new Field_Group_Repository();
	}

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action('add_meta_boxes', [$this, 'add_meta_boxes'], 10, 2);
		add_action('save_post', [$this, 'save'], 20, 2);
	}

	/**
	 * Add meta boxes to supported post type screens.
	 */
	public function add_meta_boxes(string $post_type, WP_Post $post): void {
		if (Post_Type::POST_TYPE === $post_type) {
			return;
		}

		foreach ($this->groups->for_post($post) as $group) {
			add_meta_box(
				'cfl_group_' . (int) $group['id'],
				esc_html((string) $group['title']),
				[$this, 'render'],
				$post_type,
				'normal',
				'default',
				['group' => $group]
			);
		}
	}

	/**
	 * Render fields in the editor.
	 *
	 * @param array<string, mixed> $box Meta box data.
	 */
	public function render(WP_Post $post, array $box): void {
		$group = $box['args']['group'] ?? [];

		wp_nonce_field('cfl_save_fields', 'cfl_fields_nonce');

		echo '<div class="cfl-meta-box">';
		foreach ((array) ($group['fields'] ?? []) as $field) {
			$name = (string) ($field['name'] ?? '');

			if ('' === $name) {
				continue;
			}

			$value = get_post_meta($post->ID, $name, true);
			if ('' === $value && array_key_exists('default_value', $field)) {
				$value = $field['default_value'];
			}

			$condition = $this->condition_attributes($field);
			printf(
				'<div class="cfl-field cfl-field-%s" data-field-name="%s"%s>',
				esc_attr((string) ($field['type'] ?? 'text')),
				esc_attr($name),
				$condition
			);
			printf(
				'<label class="cfl-field-label"><span>%s</span>%s</label>',
				esc_html((string) ($field['label'] ?? $name)),
				! empty($field['required']) ? ' <em>*</em>' : ''
			);
			$this->registry->render($field, $value, 'cfl_fields[' . $name . ']');
			echo '</div>';
		}
		echo '</div>';
	}

	/**
	 * Save submitted fields.
	 */
	public function save(int $post_id, WP_Post $post): void {
		if (Post_Type::POST_TYPE === $post->post_type || wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
			return;
		}

		if (! isset($_POST['cfl_fields_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cfl_fields_nonce'])), 'cfl_save_fields')) {
			return;
		}

		if (! current_user_can('edit_post', $post_id)) {
			return;
		}

		$posted = isset($_POST['cfl_fields']) && is_array($_POST['cfl_fields']) ? wp_unslash($_POST['cfl_fields']) : [];

		foreach ($this->groups->for_post($post) as $group) {
			foreach ((array) ($group['fields'] ?? []) as $field) {
				$name = (string) ($field['name'] ?? '');

				if ('' === $name) {
					continue;
				}

				$value = $this->registry->sanitize($field, $posted[$name] ?? null);

				if ('' === $value || [] === $value || null === $value) {
					delete_post_meta($post_id, $name);
				} else {
					update_post_meta($post_id, $name, $value);
				}
			}
		}
	}

	/**
	 * Build conditional visibility data attributes.
	 *
	 * @param array<string, mixed> $field Field config.
	 */
	private function condition_attributes(array $field): string {
		$conditional = $field['conditional'] ?? [];

		if (! is_array($conditional) || empty($conditional['field'])) {
			return '';
		}

		return sprintf(
			' data-cfl-condition-field="%s" data-cfl-condition-value="%s"',
			esc_attr((string) $conditional['field']),
			esc_attr((string) ($conditional['value'] ?? ''))
		);
	}
}
