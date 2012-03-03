<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php bloginfo('name'); ?><?php wp_title(); ?></title>

<!-- Meta -->
<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
<meta name="keywords" content="blog, wordpress" />
<meta name="description" content="<?php bloginfo('description'); ?>" />
<meta name="robots" content="index, follow" />
<meta name="author" content="sito" />
<meta name="MSSmartTagsPreventParsing" content="true" />

<style type="text/css" media="screen">
	@import url( <?php bloginfo('stylesheet_url'); ?> );
	<?php if (is_front_page()) : ?>
	@import url(<?php echo get_bloginfo('stylesheet_directory') . '/home.css'; ?>);
	<?php endif; ?>
</style>
<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="<?php bloginfo('atom_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php wp_head(); ?>
</head>

<body>
<?php 
    // put here the address of your feedburner feed
    // e.g. 'http://feeds.feedburner.com/raptxtit'
    // or leav it blank if you do not have one
    
    $feedburner_feed = 'http://feeds.feedburner.com/domusneminis';
?>
<div id="head">
  <div>
    <h1>// <a href="<?php bloginfo('url'); ?>/" title="Home"><?php bloginfo('name'); ?></a> }</h1>
    <h4><?php bloginfo('description'); ?></h4>
    <span class="feed"><a href="<?php bloginfo('rss2_url')?>" title="subscribe" class="rss"><?php _e('Subscribe RSS Feed', 'keepitsimple_domain') ?></a></span>
   	<?php if (is_front_page()) : ?>
   	<br style="clear: both" />
   	<div class="about">
   	<img src="<?php bloginfo('template_directory')?>/img/about.png" title="Vincenzo Russo" alt="Vincenzo Russo" height="240" />
  	<?php query_posts('post_type=page&page_id=367'); ?>
	<?php if (have_posts()): while (have_posts()) : the_post(); ?>
   	<?php the_content()?>
	<?php endwhile; ?>
	<?php endif; ?>
   	</div>
	<?php endif; ?>
   	<?php if (is_front_page()) : ?>
	<div class="page-list-home">
	<?php else : ?>
	<div class="page-list page-list-home">
	<?php endif;?>
	<?php wp_reset_query(); ?>
	<?php $args = array('post_type' => 'page', 'post__in' => array(2,3,9,15,17), 'orderby' => 'title', 'order' => 'ASC'); ?>
	<?php query_posts($args); ?>
	<?php if (have_posts()): while (have_posts()) : the_post(); ?>
		<a href="<?php the_permalink() ?>" title="<?php the_title(); ?>" rel="bookmark"><?php the_title(); ?> &raquo;</a> 
	<?php endwhile; ?>
	<?php endif; ?>
	<?php wp_reset_query(); ?>
	</div>
  </div>
</div>
<div id="gradient"></div>
<!-- end header -->