<?php get_header(); ?>

<div id="container">
  <div id="content">

  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

    <div class="post" id="post-<?php the_ID(); ?>">
      <div class="post-content">
        <h2><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
        <?php the_content(__('(more...)', 'keepitsimple_domain')); ?>
      </div>
    </div>
    
    <div class="divider"></div>
  
    <?php endwhile; else: ?>
    <p><?php _e('Sorry, no posts matched your criteria.', 'keepitsimple_domain'); ?></p>
    <?php endif; ?>
    <div class="clear">&nbsp;</div>
  </div>
  
  <?php get_sidebar(); ?>
  <?php if ( function_exists('wp_pagenavi') )	{ ?>
  <div id="pagenavi">
  <?php wp_pagenavi($before = '', $after = '', $prelabel = '', $nxtlabel = '', $pages_to_show = 5, $always_show = false); ?>
  </div>
  <?php } else { ?>
  	<div class="navigation"><p>
	<?php if(!is_single()) { ?>
   		<?php posts_nav_link(' | ', __('Newer&nbsp;Posts&nbsp;&rarr;', 'keepitsimple_domain'), __('&larr;&nbsp;Older&nbsp;Posts', 'keepitsimple_domain')); ?>
	<?php } else { ?>
		<?php previous_post_link('%link', __('&larr;&nbsp;Previous&nbsp;Post', 'keepitsimple_domain') ) ?> | <?php next_post_link('%link', __('Next&nbsp;Post&nbsp;&rarr;', 'keepitsimple_domain')) ?>
	<?php } ?>
	</p></div>
  <?php } ?>
</div>
<?php get_footer(); ?>