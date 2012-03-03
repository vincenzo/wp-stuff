<?php

// loading theme localization<?php

// loading theme localization
load_theme_textdomain('keepitsimple_domain');

if ( function_exists('register_sidebar') ) {
    register_sidebar(array(
    	'name' => 'Blog',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2>',
        'after_title' => '</h2>',
    ));
    
    register_sidebar(array(
    	'name' => 'Home',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2>',
        'after_title' => '</h2>',
    ));
    
}

// getting the number of readers from a feedburner feed
function feed_circulation($feed) {	
    if ( !function_exists('simplexml_load_file') && ($feed != ''))	{
        $xml_string = simplexml_load_file('http://api.feedburner.com/awareness/1.0/GetFeedData?uri='.$feed);
        $readers = $xml_string->feed->entry['circulation'];
    } else {
	    $readers = ''; //__('a lot', 'keepitsimple_domain');
    }
    return $readers;
}

function simple_comment_author() {
	$author_url = get_comment_author_url();
	if ($author_url) {
		return get_comment_author() . ' &rarr; <a rel="nofollow" href="' . get_comment_author_url() . '" title="' . get_comment_author() . '"><em>' . get_comment_author_url() . '</em></a>';
	}
	else {
		return get_comment_author() . ' &rarr; <em>' . __('No website') . '</em>';
	}
}

function simple_detect_sidebar() {
	wp_reset_query();
	return is_front_page() ? 'Home' : 'Blog';
}
?>