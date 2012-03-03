	<div class="sidebar">
<ul>

<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar(1) ) : else : ?>

	<li id="categories"><h2><?php _e('Categories', 'idea_domain'); ?></h2>
		<ul>
			<?php wp_list_cats('hierarchical=0'); ?>
		</ul>
	</li>

	<li id="archives"><h2><?php _e('Archives', 'idea_domain'); ?></h2>
		<ul>
			<?php wp_get_archives('type=monthly'); ?>
		</ul>
	</li>

	<li id="search"><?php include (TEMPLATEPATH . '/searchform.php'); ?></li>

<?php endif; ?>

</ul>
	</div>