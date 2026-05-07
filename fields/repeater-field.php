<?php
declare(strict_types=1);

namespace CustomFieldsLite\Fields;

use CustomFieldsLite\Includes\Abstract_Field;
use CustomFieldsLite\Includes\Field_Registry;

if (! defined('ABSPATH')) {
	exit;
}

final class Repeater_Field extends Abstract_Field {
	private Field_Registry $registry;

	public function __construct(Field_Registry $registry) {
		$this->registry = $registry;
	}

	public function type(): string {
		return 'repeater';
	}

	public function label(): string {
		return esc_html__('Repeater', 'custom-fields-lite');
	}

	public function render(array $field, mixed $value, string $name): void {
		$rows       = is_array($value) ? array_values($value) : [];
		$subfields  = is_array($field['sub_fields'] ?? null) ? $field['sub_fields'] : [];
		$field_name = (string) ($field['name'] ?? '');
		$token      = '__CFL_INDEX_' . md5($name . '|' . $field_name) . '__';

		$this->instructions($field);
		echo '<div class="cfl-repeater" data-field="' . esc_attr($field_name) . '">';
		echo '<div class="cfl-repeater-rows">';

		foreach ($rows as $index => $row) {
			$this->render_row($subfields, is_array($row) ? $row : [], $name, (string) $index);
		}

		echo '</div>';
		echo '<script type="text/template" class="cfl-repeater-template" data-index-token="' . esc_attr($token) . '">';
		$this->render_row($subfields, [], $name, $token);
		echo '</script>';
		echo '<button type="button" class="button button-primary cfl-repeater-add">' . esc_html__('Add Row', 'custom-fields-lite') . '</button>';
		echo '</div>';
	}

	/**
	 * Render a repeater row.
	 *
	 * @param array<int, array<string, mixed>> $subfields Subfields.
	 * @param array<string, mixed>            $row        Row values.
	 */
	public function render_row(array $subfields, array $row, string $base_name, string $index): void {
		echo '<div class="cfl-repeater-row">';
		echo '<div class="cfl-repeater-row-bar"><span class="dashicons dashicons-move cfl-sort-handle"></span><strong>' . esc_html__('Row', 'custom-fields-lite') . '</strong><span class="cfl-row-actions"><button type="button" class="button-link cfl-repeater-duplicate">' . esc_html__('Duplicate', 'custom-fields-lite') . '</button><button type="button" class="button-link cfl-repeater-collapse">' . esc_html__('Collapse', 'custom-fields-lite') . '</button><button type="button" class="button-link-delete cfl-repeater-remove">' . esc_html__('Remove', 'custom-fields-lite') . '</button></span></div>';
		echo '<div class="cfl-repeater-row-fields">';
		foreach ($subfields as $subfield) {
			$key       = (string) ($subfield['name'] ?? '');
			$row_value = $row[$key] ?? ($subfield['default_value'] ?? '');
			$input     = $base_name . '[' . $index . '][' . $key . ']';
			printf('<div class="cfl-field cfl-field-%s">', esc_attr((string) ($subfield['type'] ?? 'text')));
			printf('<label><span>%s</span>%s</label>', esc_html((string) ($subfield['label'] ?? $key)), ! empty($subfield['required']) ? ' <em>*</em>' : '');
			if ('wysiwyg' === ($subfield['type'] ?? '')) {
				if (! empty($subfield['instructions'])) {
					printf('<p class="description">%s</p>', esc_html((string) $subfield['instructions']));
				}
				printf('<textarea class="widefat cfl-wysiwyg-fallback" rows="6" name="%s">%s</textarea>', esc_attr($input), esc_textarea((string) $row_value));
			} else {
				$this->registry->render($subfield, $row_value, $input);
			}
			echo '</div>';
		}
		echo '</div></div>';
	}

	public function sanitize(array $field, mixed $value): mixed {
		if (! is_array($value)) {
			return [];
		}

		$subfields = is_array($field['sub_fields'] ?? null) ? $field['sub_fields'] : [];
		$rows      = [];

		foreach ($value as $row) {
			if (! is_array($row)) {
				continue;
			}

			$clean_row = [];
			foreach ($subfields as $subfield) {
				$name = (string) ($subfield['name'] ?? '');
				if ('' === $name) {
					continue;
				}
				$clean_row[$name] = $this->registry->sanitize($subfield, $row[$name] ?? null);
			}
			$rows[] = $clean_row;
		}

		return $rows;
	}
}
