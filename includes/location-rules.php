<?php
/**
 * Field group location rules.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

use WP_Post;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Matches field groups against content.
 */
final class Location_Rules {
	/**
	 * Check if rules match a post.
	 *
	 * @param array<int, array<string, string>> $rules Rules.
	 */
	public static function matches(array $rules, WP_Post $post): bool {
		if ([] === $rules) {
			return false;
		}

		foreach ($rules as $rule) {
			$param    = (string) ($rule['param'] ?? '');
			$operator = (string) ($rule['operator'] ?? '==');
			$value    = (string) ($rule['value'] ?? '');
			$actual   = '';

			if ('post_type' === $param) {
				$actual = $post->post_type;
			} elseif ('page' === $param) {
				$actual = (string) $post->ID;
			} elseif ('template' === $param) {
				$actual = (string) get_page_template_slug($post);
			}

			$matched = '!=' === $operator ? $actual !== $value : $actual === $value;

			if ($matched) {
				return true;
			}
		}

		return false;
	}
}
