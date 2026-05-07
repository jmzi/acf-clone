<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Wysiwyg_Field extends Abstract_Field {
	public function type(): string {
		return 'wysiwyg';
	}

	public function label(): string {
		return esc_html__('WYSIWYG Editor', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		wp_editor(
			(string) $value,
			'cfl_' . wp_unique_id(),
			[
				'textarea_name' => $name,
				'textarea_rows' => 8,
				'media_buttons' => true,
				'teeny'         => false,
			]
		);
	}

	public function sanitize(array $field, mixed $value): mixed {
		return is_scalar($value) ? wp_kses_post((string) $value) : '';
	}
}
