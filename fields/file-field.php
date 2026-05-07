<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;

if (! defined('ABSPATH')) {
	exit;
}

final class File_Field extends Abstract_Field {
	public function type(): string {
		return 'file';
	}

	public function label(): string {
		return esc_html__('File', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$attachment_id = absint($value);
		$file_url      = $attachment_id ? wp_get_attachment_url($attachment_id) : '';
		$this->instructions($field);
		echo '<div class="cfl-media-field" data-type="file">';
		printf('<div class="cfl-file-name">%s</div>', $file_url ? esc_html(basename($file_url)) : '');
		printf('<input type="hidden" name="%s" value="%d">', esc_attr($name), $attachment_id);
		echo '<button type="button" class="button cfl-media-select">' . esc_html__('Select file', 'custom-fields-lite') . '</button> ';
		echo '<button type="button" class="button cfl-media-clear">' . esc_html__('Clear', 'custom-fields-lite') . '</button></div>';
	}

	public function sanitize(array $field, mixed $value): mixed {
		return absint($value);
	}
}
