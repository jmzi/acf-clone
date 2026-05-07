<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Text_Field extends Abstract_Field {
	public function type(): string {
		return 'text';
	}

	public function label(): string {
		return esc_html__('Text', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		printf('<input class="widefat" type="text" name="%s" value="%s">', esc_attr($name), esc_attr((string) $value));
	}
}
