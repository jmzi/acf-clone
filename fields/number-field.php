<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Number_Field extends Abstract_Field {
	public function type(): string {
		return 'number';
	}

	public function label(): string {
		return esc_html__('Number', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		printf('<input class="widefat" type="number" step="any" name="%s" value="%s">', esc_attr($name), esc_attr((string) $value));
	}

	public function sanitize(array $field, mixed $value): mixed {
		if ('' === $value || null === $value) {
			return '';
		}

		return is_numeric($value) ? (float) $value : '';
	}
}
