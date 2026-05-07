<?php
/**
 * AJAX handlers.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Handles secure admin AJAX requests.
 */
final class Ajax {
	private Field_Registry $registry;

	public function __construct(Field_Registry $registry) {
		$this->registry = $registry;
	}

	/**
	 * Register hooks.
	 */
	public function init(): void {
		add_action('wp_ajax_cfl_duplicate_repeater_row', [$this, 'duplicate_repeater_row']);
	}

	/**
	 * Acknowledges secure repeater duplication requests.
	 *
	 * The actual DOM clone happens client-side so nested editor markup stays intact.
	 */
	public function duplicate_repeater_row(): void {
		check_ajax_referer('cfl_admin', 'nonce');

		if (! current_user_can('edit_posts')) {
			wp_send_json_error(['message' => esc_html__('Not allowed.', 'custom-fields-lite')], 403);
		}

		wp_send_json_success(['message' => esc_html__('Row duplicated.', 'custom-fields-lite')]);
	}
}
