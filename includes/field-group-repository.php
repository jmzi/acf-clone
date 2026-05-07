<?php
/**
 * Field group repository.
 *
 * @package CustomFieldsLite
 */

declare(strict_types=1);

namespace CustomFieldsLite\Includes;

use WP_Post;
use WP_Query;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Reads and normalizes field group data.
 */
final class Field_Group_Repository {
	/**
	 * Cached groups.
	 *
	 * @var array<int, array<string, mixed>>|null
	 */
	private static ?array $cache = null;

	/**
	 * Get all published field groups.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function all(): array {
		if (null !== self::$cache) {
			return self::$cache;
		}

		$query = new WP_Query(
			[
				'post_type'              => Post_Type::POST_TYPE,
				'post_status'            => 'publish',
				'posts_per_page'         => -1,
				'orderby'                => 'menu_order date',
				'order'                  => 'ASC',
				'no_found_rows'          => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			]
		);

		self::$cache = array_map([$this, 'from_post'], $query->posts);

		return self::$cache;
	}

	/**
	 * Get groups for a post.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function for_post(WP_Post $post): array {
		return array_values(
			array_filter(
				$this->all(),
				static fn (array $group): bool => Location_Rules::matches($group['location'] ?? [], $post)
			)
		);
	}

	/**
	 * Normalize field group post data.
	 *
	 * @return array<string, mixed>
	 */
	public function from_post(WP_Post $post): array {
		return [
			'id'       => $post->ID,
			'title'    => get_the_title($post),
			'fields'   => $this->fields($post->ID),
			'location' => $this->location($post->ID),
		];
	}

	/**
	 * Get group fields.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function fields(int $group_id): array {
		$fields = get_post_meta($group_id, '_cfl_fields', true);

		return is_array($fields) ? array_values($fields) : [];
	}

	/**
	 * Get location rules.
	 *
	 * @return array<int, array<string, string>>
	 */
	public function location(int $group_id): array {
		$location = get_post_meta($group_id, '_cfl_location', true);

		return is_array($location) ? array_values($location) : [];
	}

	/**
	 * Clear static cache.
	 */
	public static function clear_cache(): void {
		self::$cache = null;
	}
}
