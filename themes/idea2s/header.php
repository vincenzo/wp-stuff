<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
    <title><?php wp_title('&mdash;', true, 'right'); bloginfo('name'); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />	
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="icon" href="<?php bloginfo('template_url')?>/favicon.ico" />
	<!--<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />-->
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
	<?php wp_get_archives('type=monthly&format=link'); ?>
	<?php //comments_popup_script(); // off by default ?>
	<?php wp_head(); ?>
</head>
<body>
<div id="main-container">
<div id="topmenu-container">
<img src="<?php bloginfo('template_directory') ?>/images/idea.png" title="" alt="" /><br />
<h1><a href="<?php bloginfo('url') ?>/" title="<?php bloginfo('name'); ?> home page"><?php bloginfo('name');?></a></h1>
<h4><?php bloginfo('description');?></h4></div>
<div id="topmenu">
<ul>
	<?php wp_list_pages('depth=1&title_li=&exclude=9'); ?>
	<li class="<?php if(is_home()) { echo "current_page_item"; } else { echo "page_item"; } ?>">
<a href="<?php bloginfo('url') ?>/blog/" title="<?php bloginfo('name'); ?>">Blog</a>
	</li>
	<li class="search"><?php include(TEMPLATEPATH . "/searchform.php")?></li>
</ul>
</div>
<div class="clear"></div>
<div id="page-container"><div id="page">