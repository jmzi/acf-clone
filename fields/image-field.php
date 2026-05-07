<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class Image_Field extends Abstract_Field {
	public function type(): string {
		return 'image';
	}

	public function label(): string {
		return esc_html__('Image', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$attachment_id = absint($value);
		$preview       = $attachment_id ? wp_get_attachment_image($attachment_id, 'thumbnail') : '';
		$this->instructions($field);
		printf('<div class="cfl-media-field" data-type="image"><div class="cfl-media-preview">%s</div>', wp_kses_post($preview));
		printf('<input type="hidden" name="%s" value="%d">', esc_attr($name), $attachment_id);
		echo '<button type="button" class="button cfl-media-select">' . esc_html__('Select image', 'custom-fields-lite') . '</button> ';
		echo '<button type="button" class="button cfl-media-clear">' . esc_html__('Clear', 'custom-fields-lite') . '</button></div>';
	}

	public function sanitize(array $field, mixed $value): mixed {
		return absint($value);
	}
}
