<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Url_Field extends Abstract_Field {
	public function type(): string {
		return 'url';
	}

	public function label(): string {
		return esc_html__('URL', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		printf('<input class="widefat" type="url" name="%s" value="%s">', esc_attr($name), esc_url((string) $value));
	}

	public function sanitize(array $field, mixed $value): mixed {
		return is_scalar($value) ? esc_url_raw((string) $value) : '';
	}
}
