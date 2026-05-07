<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Checkbox_Field extends Abstract_Field {
	public function type(): string {
		return 'checkbox';
	}

	public function label(): string {
		return esc_html__('Checkbox', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$selected = is_array($value) ? array_map('strval', $value) : [];
		$this->instructions($field);
		echo '<div class="cfl-choice-list">';
		foreach ($this->choices($field) as $choice_value => $choice_label) {
			printf(
				'<label><input type="checkbox" name="%s[]" value="%s" %s> %s</label>',
				esc_attr($name),
				esc_attr($choice_value),
				checked(in_array((string) $choice_value, $selected, true), true, false),
				esc_html($choice_label)
			);
		}
		echo '</div>';
	}

	public function sanitize(array $field, mixed $value): mixed {
		if (! is_array($value)) {
			return [];
		}

		return array_values(array_map('sanitize_text_field', wp_unslash($value)));
	}
}
