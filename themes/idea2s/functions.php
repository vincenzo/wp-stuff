<?php
// Load localizatons
load_theme_textdomain('idea_domain');

if (function_exists('register_sidebar')) {
	register_sidebars(2);
}

function widget_mytheme_search() {
	echo "<li id=\"search\">";
	include(TEMPLATEPATH . '/searchform.php');
	echo "</li>";
}

if (function_exists('register_sidebar_widget')) {
	register_sidebar_widget(__('Search'), 'widget_mytheme_search');
}
?>