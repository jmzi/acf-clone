<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Textarea_Field extends Abstract_Field {
	public function type(): string {
		return 'textarea';
	}

	public function label(): string {
		return esc_html__('Textarea', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		printf('<textarea class="widefat" rows="5" name="%s">%s</textarea>', esc_attr($name), esc_textarea((string) $value));
	}

	public function sanitize(array $field, mixed $value): mixed {
		return is_scalar($value) ? sanitize_textarea_field((string) $value) : '';
	}
}
