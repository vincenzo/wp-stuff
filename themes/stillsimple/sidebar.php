<!-- begin sidebar -->

  <div id="side">
<?php 	/* Widgetized sidebar, if you have the plugin installed or WP >= 2.3 */
  if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar(simple_detect_sidebar()) ) : ?>
	<!-- Start Categories -->
	<div class="widget widget_categories">
	<h2><?php _e('Categories', 'keepitsimple_domain') ?></h2>
	 <ul>
	  <?php wp_list_categories('sort_column=name&optioncount=1&hierarchical=false&depth=1&title_li='); ?>
	 </ul>
	</div>
	<!-- End Categories -->

	<!-- Start Links -->
	<div class="widget widget_links">
	<h2><?php _e('Blog love', 'keepitsimple_domain')?></h2>
	 <ul>
	    <?php get_links('-1', '<li>', '</li>', '', FALSE, 'id', FALSE, FALSE, -1, FALSE); ?>
	</ul>
	</div>
	<!-- End Links -->
<?php endif; ?>
		
  </div>
<!-- end sidebar -->
<span class="clear"></span>