<?php
/**
 * Asset loading.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Enqueues admin assets only when needed.
 */
final class Assets {
	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action('admin_enqueue_scripts', [$this, 'admin_assets']);
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook Current admin screen hook.
	 */
	public function admin_assets(string $hook): void {
		$screen = get_current_screen();

		if (! $screen) {
			return;
		}

		$is_group_editor = Post_Type::POST_TYPE === $screen->post_type;
		$is_content_edit = in_array($screen->base, ['post', 'post-new'], true);

		if (! $is_group_editor && ! $is_content_edit) {
			return;
		}

		wp_enqueue_style(
			'cfl-admin',
			CFL_URL . 'assets/css/admin.css',
			[],
			CFL_VERSION
		);

		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_media();

		wp_enqueue_script(
			'cfl-admin',
			CFL_URL . 'assets/js/admin.js',
			['jquery', 'jquery-ui-sortable'],
			CFL_VERSION,
			true
		);

		wp_localize_script(
			'cfl-admin',
			'cflAdmin',
			[
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce'   => wp_create_nonce('cfl_admin'),
				'i18n'    => [
					'selectImage' => esc_html__('Select image', 'custom-fields-lite'),
					'selectFile'  => esc_html__('Select file', 'custom-fields-lite'),
					'useFile'     => esc_html__('Use this file', 'custom-fields-lite'),
				],
			]
		);

		if ($is_content_edit && user_can_richedit()) {
			wp_enqueue_editor();
		}
	}
}
