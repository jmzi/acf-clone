<?php
/**
 * Example theme usage for Custom Fields Lite.
 *
 * Copy the patterns below into a theme template.
 *
 * @package CustomFieldsLite
 */

if (! defined('ABSPATH')) {
	exit;
}

$headline = cfl_get_field('headline');

if ($headline) :
	?>
	<h2><?php cfl_the_field('headline'); ?></h2>
	<?php
endif;

if (cfl_have_rows('features')) :
	?>
	<ul class="features">
		<?php
		while (cfl_have_rows('features')) :
			cfl_the_row();
			?>
			<li>
				<strong><?php cfl_the_sub_field('title'); ?></strong>
				<p><?php cfl_the_sub_field('description'); ?></p>
			</li>
			<?php
		endwhile;
		?>
	</ul>
	<?php
endif;
