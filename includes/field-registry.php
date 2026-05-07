<?php
/**
 * Field registry.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

use CustomFieldsLite\Fields\Checkbox_Field;
use CustomFieldsLite\Fields\Email_Field;
use CustomFieldsLite\Fields\File_Field;
use CustomFieldsLite\Fields\Image_Field;
use CustomFieldsLite\Fields\Number_Field;
use CustomFieldsLite\Fields\Radio_Field;
use CustomFieldsLite\Fields\Repeater_Field;
use CustomFieldsLite\Fields\Select_Field;
use CustomFieldsLite\Fields\Textarea_Field;
use CustomFieldsLite\Fields\Text_Field;
use CustomFieldsLite\Fields\True_False_Field;
use CustomFieldsLite\Fields\Url_Field;
use CustomFieldsLite\Fields\Wysiwyg_Field;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Stores available field types.
 */
final class Field_Registry {
	/**
	 * Registered field handlers.
	 *
	 * @var array<string, Abstract_Field>
	 */
	private array $fields = [];

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->register_defaults();
	}

	/**
	 * Register default fields.
	 */
	private function register_defaults(): void {
		$this->register(new Text_Field());
		$this->register(new Textarea_Field());
		$this->register(new Number_Field());
		$this->register(new Email_Field());
		$this->register(new Url_Field());
		$this->register(new Select_Field());
		$this->register(new Checkbox_Field());
		$this->register(new Radio_Field());
		$this->register(new True_False_Field());
		$this->register(new Image_Field());
		$this->register(new File_Field());
		$this->register(new Wysiwyg_Field());
		$this->register(new Repeater_Field($this));
	}

	/**
	 * Register field handler.
	 */
	public function register(Abstract_Field $field): void {
		$this->fields[$field->type()] = $field;
	}

	/**
	 * Get all fields.
	 *
	 * @return array<string, Abstract_Field>
	 */
	public function all(): array {
		return $this->fields;
	}

	/**
	 * Get field handler.
	 */
	public function get(string $type): Abstract_Field {
		return $this->fields[$type] ?? $this->fields['text'];
	}

	/**
	 * Render field.
	 *
	 * @param array<string, mixed> $field Field config.
	 * @param mixed                $value Field value.
	 * @param string               $name  Input name.
	 */
	public function render(array $field, mixed $value, string $name): void {
		$this->get((string) ($field['type'] ?? 'text'))->render($field, $value, $name);
	}

	/**
	 * Sanitize field value.
	 *
	 * @param array<string, mixed> $field Field config.
	 * @param mixed                $value Raw value.
	 *
	 * @return mixed
	 */
	public function sanitize(array $field, mixed $value): mixed {
		return $this->get((string) ($field['type'] ?? 'text'))->sanitize($field, $value);
	}
}
