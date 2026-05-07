<?php
/**
 * Repeater template loop.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Lightweight ACF-like row iterator.
 */
final class Loop {
	private static ?self $instance = null;

	/**
	 * Loop stack.
	 *
	 * @var array<int, array<string, mixed>>
	 */
	private array $stack = [];

	public static function instance(): self {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Check for another row in a repeater.
	 */
	public function have_rows(string $field_name, ?int $post_id = null): bool {
		$post_id = $post_id ?: get_the_ID();
		$key     = $post_id . ':' . $field_name . ':' . count($this->stack);
		$current = end($this->stack);

		if ($current && ($current['field_name'] ?? '') === $field_name && (int) ($current['post_id'] ?? 0) === (int) $post_id) {
			if (($current['index'] + 1) < count($current['rows'])) {
				return true;
			}

			array_pop($this->stack);
			return false;
		}

		$parent_row = $this->current_row();
		$rows       = is_array($parent_row) && array_key_exists($field_name, $parent_row) ? $parent_row[$field_name] : ($post_id ? get_post_meta($post_id, $field_name, true) : []);
		$rows = is_array($rows) ? array_values($rows) : [];

		$this->stack[] = [
			'key'        => $key,
			'field_name' => $field_name,
			'post_id'    => (int) $post_id,
			'rows'       => $rows,
			'index'      => -1,
		];

		if ([] === $rows) {
			array_pop($this->stack);
			return false;
		}

		return true;
	}

	/**
	 * Advance the current loop.
	 */
	public function the_row(): void {
		$index = array_key_last($this->stack);

		if (null === $index) {
			return;
		}

		++$this->stack[$index]['index'];
	}

	/**
	 * Get a sub field from current row.
	 *
	 * @return mixed
	 */
	public function get_sub_field(string $field_name): mixed {
		$row = $this->current_row();

		return is_array($row) ? ($row[$field_name] ?? null) : null;
	}

	/**
	 * Get the current row from the active loop.
	 *
	 * @return array<string, mixed>|null
	 */
	private function current_row(): ?array {
		$current = end($this->stack);

		if (! $current) {
			return null;
		}

		$row_index = (int) $current['index'];
		$row       = $current['rows'][$row_index] ?? null;

		return is_array($row) ? $row : null;
	}
}
