<?php get_header(); ?>
	<div class="narrowcolumn">
		<?php if(have_posts()) : ?><?php while(have_posts()) : the_post(); ?>
		<div class="post" id="post-<?php the_ID(); ?>">
		    <h2><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a></h2>
			<div class="entry">
			<div class="postinfo">
				<ul>
					<?php if (function_exists('the_views')) { ?><li class=post-views><?php the_views() ?></li><?php  } ?>
                    <?php if ('open' == $post->comment_status) { ?><li class="comments-feed"><?php post_comments_feed_link(__('Follow comments', 'idea_domain'), get_the_ID())?></li><?php } ?>
					<?php edit_post_link(__('e'), '<li class="edit">', '</li>'); ?>
				</ul>
			</div>
				<?php the_content(__('Read more', 'idea_domain') . " &raquo;"); ?>
			</div>
			<div class="postinfo">
			<ul>
			<li class="post-ratings"><?php if(function_exists('the_ratings')) { the_ratings(); } ?></li>
			</ul>
			</div>
					
		</div>
		<div class="clear"></div>
		<div class="comments-template">
			<?php comments_template(); ?>
		</div>
		<div class="clear"></div>
		<?php endwhile; ?>
		<?php else : ?>
		<div class="post"><h2><?php _e('Not Found', 'idea_domain'); ?></h2><div class="entry"><?php _e('You are looking for something which just is not here! Fear not however, errors are to be expected, and luckily there are tools on the sidebar for you to use in your search for what you need.', 'idea_domain');?></div></div>
		<?php endif; ?>
	</div><!-- end narrwocolumn -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>
