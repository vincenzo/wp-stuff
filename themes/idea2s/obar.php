<div class="sidebar"><ul id="feed"><li><h2><?php include(TEMPLATEPATH . "/feeds.php")?></h2></li></ul>
<ul>
<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar(2) ) : else : ?>
<?php get_links_list(); ?>
<?php endif; ?>
</ul></div>