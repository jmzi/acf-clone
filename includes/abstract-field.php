<?php
/**
 * Base field class.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Base renderer and sanitizer for fields.
 */
abstract class Abstract_Field {
	/**
	 * Field type key.
	 */
	abstract public function type(): string;

	/**
	 * Field label.
	 */
	abstract public function label(): string;

	/**
	 * Render field control.
	 *
	 * @param array<string, mixed> $field Field config.
	 * @param mixed                $value Field value.
	 * @param string               $name  Input name.
	 */
	abstract public function render(array $field, mixed $value, string $name): void;

	/**
	 * Sanitize submitted value.
	 *
	 * @param array<string, mixed> $field Field config.
	 * @param mixed                $value Raw value.
	 *
	 * @return mixed
	 */
	public function sanitize(array $field, mixed $value): mixed {
		return is_scalar($value) ? sanitize_text_field((string) $value) : '';
	}

	/**
	 * Convert field choices into an associative array.
	 *
	 * @param array<string, mixed> $field Field config.
	 *
	 * @return array<string, string>
	 */
	protected function choices(array $field): array {
		$choices = [];
		$raw     = (string) ($field['choices'] ?? '');
		$lines   = preg_split('/\r\n|\r|\n/', $raw) ?: [];

		foreach ($lines as $line) {
			$line = trim($line);

			if ('' === $line) {
				continue;
			}

			if (str_contains($line, ':')) {
				[$value, $label] = array_map('trim', explode(':', $line, 2));
			} else {
				$value = $line;
				$label = $line;
			}

			$choices[$value] = $label;
		}

		return $choices;
	}

	/**
	 * Render field help text.
	 *
	 * @param array<string, mixed> $field Field config.
	 */
	protected function instructions(array $field): void {
		if (! empty($field['instructions'])) {
			printf('<p class="description">%s</p>', esc_html((string) $field['instructions']));
		}
	}
}
