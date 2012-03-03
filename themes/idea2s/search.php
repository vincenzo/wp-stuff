<?php get_header(); ?>
	<div class="narrowcolumn">
		<?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		    <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
			<div class="entry">
			<div class="postinfo">
				<ul>
					<li class="postdate"><?php the_time('d M \'y') ?></li>
					<?php if (function_exists('the_views')) { ?><li class=post-views><?php the_views() ?></li><?php  } ?>
					<li class="comments-link"><?php comments_popup_link('0 ' . __('Comments', 'idea_domain'), '1 ' . __('Comment', 'idea_domain'), '% ' . __('Comments', 'idea_domain')); ?></li>
                    <?php if ('open' == $post->comment_status) { ?><li class="comments-feed"><?php post_comments_feed_link(__('Follow comments', 'idea_domain'), get_the_ID())?></li><?php } ?>
					<?php edit_post_link(__('e'), '<li class="edit">', '</li>'); ?>
				</ul>
			</div>
				<?php 
				    if (function_exists('the_thumb')) the_thumb(); 
    				the_excerpt(); 
    			?>
				<?php echo '<a class="more-link" href="'. get_permalink() . '#more-' . get_the_ID() .'">' . __('Read more', 'idea_domain') . ' &raquo; </a>';?>
			</div>
			<div class="postinfo">
			<ul>
			<li class="post-ratings"><?php if(function_exists('the_ratings')) { the_ratings(); } ?></li>
			</ul>
			</div>
		</div>
		<div class="clear"></div>
		<?php endwhile; ?>
		<div class="browse"><p><?php posts_nav_link() ?></p></div>
		<?php else : ?>
		<div class="post"><h2><?php _e('Not Found', 'idea_domain'); ?></h2><div class="entry"><?php _e('You are looking for something which just is not here! Fear not however, errors are to be expected, and luckily there are tools on the sidebar for you to use in your search for what you need.', 'idea_domain');?></div></div>
		<?php endif; ?>
	</div><!-- end narrwocolumn -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>