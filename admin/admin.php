<?php
/**
 * Admin field group editor.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Admin;

use CustomFieldsLite\Includes\Field_Group_Repository;
use CustomFieldsLite\Includes\Field_Registry;
use CustomFieldsLite\Includes\Post_Type;
use WP_Post;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Admin screens and save logic.
 */
final class Admin {
	private Field_Registry $registry;

	public function __construct(Field_Registry $registry) {
		$this->registry = $registry;
	}

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action('add_meta_boxes_' . Post_Type::POST_TYPE, [$this, 'add_meta_boxes']);
		add_action('save_post_' . Post_Type::POST_TYPE, [$this, 'save_field_group'], 10, 2);
		add_filter('post_row_actions', [$this, 'row_actions'], 10, 2);
		add_action('admin_action_cfl_duplicate_group', [$this, 'duplicate_group']);
		add_action('admin_menu', [$this, 'admin_menu']);
		add_action('admin_post_cfl_export_groups', [$this, 'export_groups']);
		add_action('admin_post_cfl_import_groups', [$this, 'import_groups']);
	}

	/**
	 * Add field group editor boxes.
	 */
	public function add_meta_boxes(): void {
		add_meta_box('cfl_builder', esc_html__('Fields', 'custom-fields-lite'), [$this, 'render_builder'], Post_Type::POST_TYPE, 'normal', 'high');
		add_meta_box('cfl_locations', esc_html__('Location Rules', 'custom-fields-lite'), [$this, 'render_locations'], Post_Type::POST_TYPE, 'normal', 'default');
	}

	/**
	 * Render field builder.
	 */
	public function render_builder(WP_Post $post): void {
		$fields = get_post_meta($post->ID, '_cfl_fields', true);
		$fields = is_array($fields) ? array_values($fields) : [];

		wp_nonce_field('cfl_save_group', 'cfl_group_nonce');

		echo '<div class="cfl-tabs"><button type="button" class="button cfl-tab is-active" data-tab="fields">' . esc_html__('Fields', 'custom-fields-lite') . '</button><button type="button" class="button cfl-tab" data-tab="tools">' . esc_html__('Tools', 'custom-fields-lite') . '</button></div>';
		echo '<div class="cfl-tab-panel is-active" data-panel="fields">';
		echo '<div class="cfl-field-builder" data-next-index="' . esc_attr((string) max(1, count($fields))) . '">';
		echo '<div class="cfl-builder-rows">';
		foreach ($fields as $index => $field) {
			$this->render_field_settings($field, 'cfl_group_fields[' . $index . ']');
		}
		echo '</div>';
		echo '<script type="text/template" id="cfl-field-template">';
		$this->render_field_settings([], 'cfl_group_fields[__INDEX__]');
		echo '</script>';
		echo '<p><button type="button" class="button button-primary cfl-add-field">' . esc_html__('Add Field', 'custom-fields-lite') . '</button></p>';
		echo '</div></div>';

		echo '<div class="cfl-tab-panel" data-panel="tools">';
		echo '<p>' . esc_html__('Export and import field groups from the Custom Fields Lite Tools submenu.', 'custom-fields-lite') . '</p>';
		echo '</div>';
	}

	/**
	 * Render one field settings row.
	 *
	 * @param array<string, mixed> $field Field settings.
	 */
	private function render_field_settings(array $field, string $base): void {
		$type       = (string) ($field['type'] ?? 'text');
		$name       = (string) ($field['name'] ?? '');
		$label      = (string) ($field['label'] ?? '');
		$subfields  = is_array($field['sub_fields'] ?? null) ? $field['sub_fields'] : [];
		$collapsed  = '' !== $label ? $label : esc_html__('New Field', 'custom-fields-lite');
		$field_keys = array_keys($this->registry->all());

		echo '<div class="cfl-builder-row">';
		echo '<div class="cfl-builder-row-header"><span class="dashicons dashicons-move cfl-sort-handle"></span><strong class="cfl-builder-title">' . esc_html($collapsed) . '</strong><button type="button" class="button-link cfl-builder-toggle">' . esc_html__('Toggle', 'custom-fields-lite') . '</button><button type="button" class="button-link-delete cfl-builder-remove">' . esc_html__('Remove', 'custom-fields-lite') . '</button></div>';
		echo '<div class="cfl-builder-row-body">';
		echo '<div class="cfl-settings-grid">';
		$this->input($base . '[label]', esc_html__('Label', 'custom-fields-lite'), $label);
		$this->input($base . '[name]', esc_html__('Name', 'custom-fields-lite'), $name);
		echo '<label><span>' . esc_html__('Type', 'custom-fields-lite') . '</span><select name="' . esc_attr($base . '[type]') . '" class="cfl-field-type">';
		foreach ($field_keys as $field_type) {
			printf('<option value="%s" %s>%s</option>', esc_attr($field_type), selected($type, $field_type, false), esc_html($this->registry->get($field_type)->label()));
		}
		echo '</select></label>';
		$this->input($base . '[default_value]', esc_html__('Default Value', 'custom-fields-lite'), (string) ($field['default_value'] ?? ''));
		echo '<label class="cfl-full"><span>' . esc_html__('Instructions', 'custom-fields-lite') . '</span><textarea name="' . esc_attr($base . '[instructions]') . '">' . esc_textarea((string) ($field['instructions'] ?? '')) . '</textarea></label>';
		echo '<label class="cfl-full cfl-choice-setting"><span>' . esc_html__('Choices', 'custom-fields-lite') . '</span><textarea name="' . esc_attr($base . '[choices]') . '" placeholder="value: Label">' . esc_textarea((string) ($field['choices'] ?? '')) . '</textarea></label>';
		echo '<label><input type="checkbox" name="' . esc_attr($base . '[required]') . '" value="1" ' . checked(! empty($field['required']), true, false) . '> ' . esc_html__('Required', 'custom-fields-lite') . '</label>';
		$this->input($base . '[conditional][field]', esc_html__('Show when field', 'custom-fields-lite'), (string) ($field['conditional']['field'] ?? ''));
		$this->input($base . '[conditional][value]', esc_html__('Equals value', 'custom-fields-lite'), (string) ($field['conditional']['value'] ?? ''));
		echo '</div>';
		echo '<div class="cfl-subfields" data-subfield-next="' . esc_attr((string) max(1, count($subfields))) . '">';
		echo '<h4>' . esc_html__('Repeater Sub-fields', 'custom-fields-lite') . '</h4>';
		echo '<div class="cfl-subfield-rows">';
		foreach ($subfields as $index => $subfield) {
			$this->render_field_settings($subfield, $base . '[sub_fields][' . $index . ']');
		}
		echo '</div>';
		echo '<button type="button" class="button cfl-add-subfield">' . esc_html__('Add Sub-field', 'custom-fields-lite') . '</button>';
		echo '</div></div></div>';
	}

	/**
	 * Render a labelled input.
	 */
	private function input(string $name, string $label, string $value): void {
		printf('<label><span>%s</span><input type="text" name="%s" value="%s"></label>', esc_html($label), esc_attr($name), esc_attr($value));
	}

	/**
	 * Render location rules.
	 */
	public function render_locations(WP_Post $post): void {
		$location = get_post_meta($post->ID, '_cfl_location', true);
		$location = is_array($location) ? array_values($location) : [['param' => 'post_type', 'operator' => '==', 'value' => 'post']];
		$post_types = get_post_types(['public' => true], 'objects');
		$pages      = get_pages(['sort_column' => 'post_title']);

		echo '<div class="cfl-location-rules" data-next-index="' . esc_attr((string) count($location)) . '">';
		echo '<div class="cfl-location-rows">';
		foreach ($location as $index => $rule) {
			$this->render_location_rule($rule, $index, $post_types, $pages);
		}
		echo '</div>';
		echo '<script type="text/template" id="cfl-location-template">';
		$this->render_location_rule([], '__INDEX__', $post_types, $pages);
		echo '</script>';
		echo '<button type="button" class="button cfl-add-location">' . esc_html__('Add Location Rule', 'custom-fields-lite') . '</button>';
		echo '</div>';
	}

	/**
	 * Render location rule.
	 *
	 * @param array<string, mixed> $rule Rule.
	 * @param array<string, object> $post_types Post types.
	 * @param array<int, WP_Post>   $pages Pages.
	 */
	private function render_location_rule(array $rule, string|int $index, array $post_types, array $pages): void {
		$base  = 'cfl_location[' . $index . ']';
		$param = (string) ($rule['param'] ?? 'post_type');
		$value = (string) ($rule['value'] ?? 'post');

		echo '<div class="cfl-location-row">';
		echo '<select name="' . esc_attr($base . '[param]') . '" class="cfl-location-param">';
		foreach (['post_type' => __('Post Type', 'custom-fields-lite'), 'page' => __('Page', 'custom-fields-lite'), 'template' => __('Template', 'custom-fields-lite')] as $key => $label) {
			printf('<option value="%s" %s>%s</option>', esc_attr($key), selected($param, $key, false), esc_html($label));
		}
		echo '</select><select name="' . esc_attr($base . '[operator]') . '"><option value="==" ' . selected((string) ($rule['operator'] ?? '=='), '==', false) . '>' . esc_html__('equals', 'custom-fields-lite') . '</option><option value="!=" ' . selected((string) ($rule['operator'] ?? '=='), '!=', false) . '>' . esc_html__('does not equal', 'custom-fields-lite') . '</option></select>';
		echo '<select name="' . esc_attr($base . '[value]') . '" class="cfl-location-value cfl-value-post-type">';
		foreach ($post_types as $post_type) {
			printf('<option value="%s" %s>%s</option>', esc_attr($post_type->name), selected($value, $post_type->name, false), esc_html($post_type->labels->singular_name));
		}
		echo '</select><select name="' . esc_attr($base . '[value_page]') . '" class="cfl-location-value cfl-value-page">';
		foreach ($pages as $page) {
			printf('<option value="%d" %s>%s</option>', (int) $page->ID, selected($value, (string) $page->ID, false), esc_html($page->post_title));
		}
		echo '</select><input class="cfl-location-value cfl-value-template" type="text" name="' . esc_attr($base . '[value_template]') . '" value="' . esc_attr($value) . '" placeholder="template-name.php">';
		echo '<button type="button" class="button-link-delete cfl-remove-location">' . esc_html__('Remove', 'custom-fields-lite') . '</button></div>';
	}

	/**
	 * Save field group settings.
	 */
	public function save_field_group(int $post_id, WP_Post $post): void {
		if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
			return;
		}

		if (! isset($_POST['cfl_group_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['cfl_group_nonce'])), 'cfl_save_group')) {
			return;
		}

		if (! current_user_can('edit_post', $post_id)) {
			return;
		}

		$fields   = isset($_POST['cfl_group_fields']) && is_array($_POST['cfl_group_fields']) ? wp_unslash($_POST['cfl_group_fields']) : [];
		$location = isset($_POST['cfl_location']) && is_array($_POST['cfl_location']) ? wp_unslash($_POST['cfl_location']) : [];

		update_post_meta($post_id, '_cfl_fields', $this->sanitize_fields($fields));
		update_post_meta($post_id, '_cfl_location', $this->sanitize_locations($location));
		Field_Group_Repository::clear_cache();
	}

	/**
	 * Sanitize field definitions.
	 *
	 * @param array<int|string, mixed> $fields Raw fields.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function sanitize_fields(array $fields): array {
		$clean = [];
		$types = array_keys($this->registry->all());

		foreach ($fields as $field) {
			if (! is_array($field)) {
				continue;
			}

			$name = sanitize_key((string) ($field['name'] ?? ''));
			if ('' === $name) {
				continue;
			}

			$type = sanitize_key((string) ($field['type'] ?? 'text'));
			$type = in_array($type, $types, true) ? $type : 'text';

			$clean[] = [
				'label'         => sanitize_text_field((string) ($field['label'] ?? $name)),
				'name'          => $name,
				'type'          => $type,
				'instructions'  => sanitize_textarea_field((string) ($field['instructions'] ?? '')),
				'required'      => empty($field['required']) ? 0 : 1,
				'default_value' => sanitize_text_field((string) ($field['default_value'] ?? '')),
				'choices'       => sanitize_textarea_field((string) ($field['choices'] ?? '')),
				'conditional'   => [
					'field' => sanitize_key((string) ($field['conditional']['field'] ?? '')),
					'value' => sanitize_text_field((string) ($field['conditional']['value'] ?? '')),
				],
				'sub_fields'    => $this->sanitize_fields(is_array($field['sub_fields'] ?? null) ? $field['sub_fields'] : []),
			];
		}

		return $clean;
	}

	/**
	 * Sanitize locations.
	 *
	 * @param array<int|string, mixed> $rules Raw rules.
	 *
	 * @return array<int, array<string, string>>
	 */
	private function sanitize_locations(array $rules): array {
		$clean = [];

		foreach ($rules as $rule) {
			if (! is_array($rule)) {
				continue;
			}

			$param = sanitize_key((string) ($rule['param'] ?? 'post_type'));
			$value = (string) ($rule['value'] ?? '');
			if ('page' === $param) {
				$value = (string) absint($rule['value_page'] ?? $value);
			} elseif ('template' === $param) {
				$value = sanitize_file_name((string) ($rule['value_template'] ?? $value));
			} else {
				$value = sanitize_key($value);
			}

			if ('' === $value) {
				continue;
			}

			$clean[] = [
				'param'    => in_array($param, ['post_type', 'page', 'template'], true) ? $param : 'post_type',
				'operator' => '!=' === ($rule['operator'] ?? '') ? '!=' : '==',
				'value'    => $value,
			];
		}

		return $clean;
	}

	/**
	 * Add duplicate action.
	 *
	 * @param array<string, string> $actions Row actions.
	 *
	 * @return array<string, string>
	 */
	public function row_actions(array $actions, WP_Post $post): array {
		if (Post_Type::POST_TYPE !== $post->post_type || ! current_user_can('edit_post', $post->ID)) {
			return $actions;
		}

		$url = wp_nonce_url(admin_url('admin.php?action=cfl_duplicate_group&post=' . $post->ID), 'cfl_duplicate_group_' . $post->ID);
		$actions['cfl_duplicate'] = '<a href="' . esc_url($url) . '">' . esc_html__('Duplicate', 'custom-fields-lite') . '</a>';

		return $actions;
	}

	/**
	 * Duplicate a field group.
	 */
	public function duplicate_group(): void {
		$post_id = absint($_GET['post'] ?? 0);

		if (! $post_id || ! current_user_can('edit_post', $post_id) || ! check_admin_referer('cfl_duplicate_group_' . $post_id)) {
			wp_die(esc_html__('You are not allowed to duplicate this field group.', 'custom-fields-lite'));
		}

		$post = get_post($post_id);
		if (! $post || Post_Type::POST_TYPE !== $post->post_type) {
			wp_die(esc_html__('Invalid field group.', 'custom-fields-lite'));
		}

		$new_id = wp_insert_post(
			[
				'post_type'   => Post_Type::POST_TYPE,
				'post_status' => 'draft',
				'post_title'  => sprintf('%s %s', $post->post_title, __('Copy', 'custom-fields-lite')),
			]
		);

		if ($new_id && ! is_wp_error($new_id)) {
			update_post_meta($new_id, '_cfl_fields', get_post_meta($post_id, '_cfl_fields', true));
			update_post_meta($new_id, '_cfl_location', get_post_meta($post_id, '_cfl_location', true));
			wp_safe_redirect(get_edit_post_link($new_id, 'raw'));
			exit;
		}

		wp_safe_redirect(admin_url('edit.php?post_type=' . Post_Type::POST_TYPE));
		exit;
	}

	/**
	 * Register tools page.
	 */
	public function admin_menu(): void {
		add_submenu_page(
			'edit.php?post_type=' . Post_Type::POST_TYPE,
			esc_html__('Tools', 'custom-fields-lite'),
			esc_html__('Tools', 'custom-fields-lite'),
			'manage_options',
			'cfl-tools',
			[$this, 'render_tools']
		);
	}

	/**
	 * Render import/export screen.
	 */
	public function render_tools(): void {
		echo '<div class="wrap"><h1>' . esc_html__('Custom Fields Lite Tools', 'custom-fields-lite') . '</h1>';
		echo '<h2>' . esc_html__('Export', 'custom-fields-lite') . '</h2><p><a class="button button-primary" href="' . esc_url(wp_nonce_url(admin_url('admin-post.php?action=cfl_export_groups'), 'cfl_export_groups')) . '">' . esc_html__('Download JSON', 'custom-fields-lite') . '</a></p>';
		echo '<h2>' . esc_html__('Import', 'custom-fields-lite') . '</h2><form method="post" enctype="multipart/form-data" action="' . esc_url(admin_url('admin-post.php')) . '">';
		wp_nonce_field('cfl_import_groups');
		echo '<input type="hidden" name="action" value="cfl_import_groups"><input type="file" name="cfl_import" accept="application/json"> <button class="button">' . esc_html__('Import JSON', 'custom-fields-lite') . '</button></form></div>';
	}

	/**
	 * Export field groups as JSON.
	 */
	public function export_groups(): void {
		if (! current_user_can('manage_options') || ! check_admin_referer('cfl_export_groups')) {
			wp_die(esc_html__('Not allowed.', 'custom-fields-lite'));
		}

		$groups = (new Field_Group_Repository())->all();
		nocache_headers();
		header('Content-Type: application/json; charset=' . get_option('blog_charset'));
		header('Content-Disposition: attachment; filename=custom-fields-lite-export.json');
		echo wp_json_encode($groups, JSON_PRETTY_PRINT);
		exit;
	}

	/**
	 * Import field groups from JSON.
	 */
	public function import_groups(): void {
		if (! current_user_can('manage_options') || ! check_admin_referer('cfl_import_groups')) {
			wp_die(esc_html__('Not allowed.', 'custom-fields-lite'));
		}

		$tmp_name = $_FILES['cfl_import']['tmp_name'] ?? '';
		if (! is_uploaded_file($tmp_name)) {
			wp_safe_redirect(admin_url('edit.php?post_type=' . Post_Type::POST_TYPE . '&page=cfl-tools'));
			exit;
		}

		$contents = file_get_contents($tmp_name);
		$groups   = json_decode((string) $contents, true);

		if (is_array($groups)) {
			foreach ($groups as $group) {
				if (! is_array($group)) {
					continue;
				}
				$new_id = wp_insert_post(
					[
						'post_type'   => Post_Type::POST_TYPE,
						'post_status' => 'draft',
						'post_title'  => sanitize_text_field((string) ($group['title'] ?? __('Imported Field Group', 'custom-fields-lite'))),
					]
				);
				if ($new_id && ! is_wp_error($new_id)) {
					update_post_meta($new_id, '_cfl_fields', $this->sanitize_fields(is_array($group['fields'] ?? null) ? $group['fields'] : []));
					update_post_meta($new_id, '_cfl_location', $this->sanitize_locations(is_array($group['location'] ?? null) ? $group['location'] : []));
				}
			}
		}

		wp_safe_redirect(admin_url('edit.php?post_type=' . Post_Type::POST_TYPE . '&page=cfl-tools'));
		exit;
	}
}
