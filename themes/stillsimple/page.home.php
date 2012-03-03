<?php
/*
Template Name: Home
*/
?>
<?php get_header(); ?>

<div id="container">
  <div id="content">
  <h2 class="blog">from the <a href="<?php bloginfo('url');?>/blog/"><?php _e('Blog', 'keepitsimple_domain')?></a></h2>
  <?php query_posts('post_type=post&showposts=5'); ?>
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <div class="post" id="post-<?php the_ID(); ?>">
      <div class="post-content">
        <h2><a href="<?php the_permalink() ?>" title="<?php the_title(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
        <div class="post-meta">
        	<?php edit_post_link('Edit', '', ''); ?> <span class="separator">//</span> <?php the_time('d.m.y') ?> <span class="separator">//</span> <?php the_category(', ') ?> <span class="separator">//</span> <?php comments_popup_link(__('Comment', 'keepitsimple_domain') . "!", __('One comment', 'keepitsimple_domain'), __('<strong>%</strong> comments')); ?>
        </div>
        <?php the_excerpt(__('(more...)', 'keepitsimple_domain')); ?>
      </div>
      <span class="clear"></span>
    </div>    
    <?php endwhile; else: ?>
    <p><?php _e('Sorry, no posts matched your criteria.', 'keepitsimple_domain'); ?></p>
    <?php endif; ?>
    <div class="clear">&nbsp;</div>
  </div>
  
  <?php get_sidebar(); ?>
  <div class="navigation"><p>
  <a href="<?php bloginfo('url');?>/blog/"><?php _e('More on the Blog', 'keepitsimple_domain')?> &rarr;</a>
  </p></div>
</div>
<?php get_footer(); ?>