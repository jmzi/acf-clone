<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class True_False_Field extends Abstract_Field {
	public function type(): string {
		return 'true_false';
	}

	public function label(): string {
		return esc_html__('True / False', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		printf(
			'<label class="cfl-toggle"><input type="checkbox" name="%s" value="1" %s><span></span> %s</label>',
			esc_attr($name),
			checked((bool) $value, true, false),
			esc_html__('Enabled', 'custom-fields-lite')
		);
	}

	public function sanitize(array $field, mixed $value): mixed {
		return empty($value) ? 0 : 1;
	}
}
