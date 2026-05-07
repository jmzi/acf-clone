<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Email_Field extends Abstract_Field {
	public function type(): string {
		return 'email';
	}

	public function label(): string {
		return esc_html__('Email', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		printf('<input class="widefat" type="email" name="%s" value="%s">', esc_attr($name), esc_attr((string) $value));
	}

	public function sanitize(array $field, mixed $value): mixed {
		return is_scalar($value) ? sanitize_email((string) $value) : '';
	}
}
