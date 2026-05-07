<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Radio_Field extends Abstract_Field {
	public function type(): string {
		return 'radio';
	}

	public function label(): string {
		return esc_html__('Radio', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		echo '<div class="cfl-choice-list">';
		foreach ($this->choices($field) as $choice_value => $choice_label) {
			printf(
				'<label><input type="radio" name="%s" value="%s" %s> %s</label>',
				esc_attr($name),
				esc_attr($choice_value),
				checked((string) $value, (string) $choice_value, false),
				esc_html($choice_label)
			);
		}
		echo '</div>';
	}
}
