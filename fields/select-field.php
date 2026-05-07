<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Select_Field extends Abstract_Field {
	public function type(): string {
		return 'select';
	}

	public function label(): string {
		return esc_html__('Select', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$this->instructions($field);
		echo '<select class="widefat" name="' . esc_attr($name) . '">';
		echo '<option value="">' . esc_html__('Select', 'custom-fields-lite') . '</option>';
		foreach ($this->choices($field) as $choice_value => $choice_label) {
			printf('<option value="%s" %s>%s</option>', esc_attr($choice_value), selected((string) $value, (string) $choice_value, false), esc_html($choice_label));
		}
		echo '</select>';
	}
}
