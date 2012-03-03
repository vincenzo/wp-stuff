<?php

/*
 * Plugin Name: Global Search
 * Plugin URI: http://neminis.org/software/wordpress/mu-plugin-global-search/
 * Description: Replace the normal search of a blog in WPMU with a global search, i.e. a search through all the blogs in the WPMU install. Usually you might want such a behavior on the main blog of the network. 
 * Version: 0.1
 * Author: Vincenzo Russo
 * Author URI: http://neminis.org/
 */

/* This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.
   This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. */

global $wpdb;
$prefix_end_char_index = stripos($wpdb->prefix, "_");
$base_prefix = substr($wpdb->prefix, 0, $prefix_end_char_index + 1);

/*
 *  Build the union of all posts table
 */
function gs_build_union_table() {
    global $wpdb;
    global $base_prefix;    
        
    $blogs_table = $base_prefix . "blogs";
    
    /* posts_fields in WP2.6.x */
    $posts_fields = "ID, post_author, post_date, post_date_gmt, post_content, post_title, post_category, post_excerpt, 
    post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, 
    post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, 
    comment_count";

    $q = "SELECT blog_id FROM $blogs_table";
    $ids = $wpdb->get_results($q, ARRAY_A);
    $n_ids = sizeof($ids);
    
    $union = "(";
    for ($i = 0; $i < $n_ids; $i++) {
        $id = $ids[$i]['blog_id'];
        $local_post_table = $base_prefix . "$id" . "_posts";
        $union .= "(SELECT $id AS blog_id, $posts_fields FROM $local_post_table)";
        if ($i < $n_ids - 1) {
            $union .= " UNION ";
        }
    }    
    $union .= ") as gs_posts ";    
    
    return $union;
}

/*
 *  Replace the table normally used to perform the search with the union of the all posts table
 */
function gs_replace_relation($query) {
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
        $union = gs_build_union_table();
        $nq = preg_replace("`FROM ([a-z0-9_]+)posts`", "FROM " . $union, $query);
        $nq = preg_replace("`([a-z0-9_]+)posts\.`", "gs_posts.", $nq);
        return $nq;
    }
    else {
        return $query;
    }
}

/*
 *  Replace the classical wp_posts.* with gs_posts.* in the select
 */
function gs_replace_fields($fields) {
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
        return "gs_posts.*";
    }
    else {
        return $fields;
    }
}

/*
 *  User to add a sentence like "From FooBarBlog" on top of the contents of the posts
 *  that belong to blogs other than the one is performing the search operation
 *
 *  WARNING: only works if the search.php of the active theme use 
 *  the_excerpt function instead of the_content function.
 */
function gs_augment_search_excerpt($content) {
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
    
        global $post;
        global $blog_id;
        
        $post_blog_id = $post->blog_id; // the ID of the blog which the post belongs to
        $real_blog_id = $blog_id;       // the ID of the blog that is performing the search operation
    
        // if the two IDs are equal, just return the permalink that WP already built 
        if ($post_blog_id === $real_blog_id) return $content;
    
        // otherwise, temporary switch to the blog of the post
        // get the right permalink
        // and switch back to the original blog
        switch_to_blog($post_blog_id);
        $content = "<p id='gs_external_post'>" . __('From') . " " . "<a href='" . trailingslashit(get_bloginfo('wpurl')) . "' title='" . get_bloginfo('name') . "' alt='" . get_bloginfo('name') . "'>" . get_bloginfo('name') . "</a></p>" . $content;
        switch_to_blog($real_blog_id);

    }
    return $content;
}


/*
 *  Fix the permalinks of the posts that belong to blogs other than the one is
 *  performing the search operation
 */
function gs_fix_permalinks($permalink) {    
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
    
        global $post;
        global $blog_id;
        
        $post_blog_id = $post->blog_id; // the ID of the blog which the post belongs to
        $real_blog_id = $blog_id;       // the ID of the blog that is performing the search operation
    
        // if the two IDs are equal, just return the permalink that WP already built 
        if ($post_blog_id === $real_blog_id) return $permalink;
    
        // otherwise, temporary switch to the blog of the post
        // get the right permalink
        // and switch back to the original blog
        switch_to_blog($post_blog_id);
        $permalink = get_permalink($post->ID);
        switch_to_blog($real_blog_id);

    }
    return $permalink;
}

/*
 *  Fix the get_edit_post_links of the posts that belong to blogs other than the one is
 *  performing the search operation
 */
function gs_fix_get_edit_post_links($edit_post_link) {
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
    
        global $post;
        global $blog_id;
        
        $post_blog_id = $post->blog_id; // the ID of the blog which the post belongs to
        $real_blog_id = $blog_id;       // the ID of the blog that is performing the search operation
  
        // if the two IDs are equal, just return the permalink that WP already built 
        if ($post_blog_id === $real_blog_id) return $edit_post_link;
    
        // otherwise, temporary switch to the blog of the post
        // get the right permalink
        // and switch back to the original blog
        switch_to_blog($post_blog_id);
        $edit_post_link = get_edit_post_link($post->ID);
        switch_to_blog($real_blog_id);
    }
    return $edit_post_link;
}

/*
 *  Fix the _edit_post_links of the posts that belong to blogs other than the one is
 *  performing the search operation
 */
function gs_fix_edit_post_links($edit_post_link) {
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
    
        $anchor_text = "";
        preg_match('#<a.+>(.+)</a>#', $edit_post_link, $anchor_text);
        $anchor_text = $anchor_text[1];
    
        global $post;
        global $blog_id;
        
        $post_blog_id = $post->blog_id; // the ID of the blog which the post belongs to
        $real_blog_id = $blog_id;       // the ID of the blog that is performing the search operation
  
        // if the two IDs are equal, just return the permalink that WP already built 
        if ($post_blog_id === $real_blog_id) return $edit_post_link;
    
        // otherwise, temporary switch to the blog of the post
        // get the right permalink
        // and switch back to the original blog
        switch_to_blog($post_blog_id);
        $edit_post_link = edit_post_link($anchor_text, '', '');;
        switch_to_blog($real_blog_id);            
        
    }
    return $edit_post_link;
}


/*
 *  Fix the categories list of the posts that belong to blogs other than the one is
 *  performing the search operation
 */

function gs_fix_post_categories($categories) {
    
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
    
        global $post;
        global $blog_id;
        
        $post_blog_id = $post->blog_id; // the ID of the blog which the post belongs to
        $real_blog_id = $blog_id;       // the ID of the blog that is performing the search operation
    
        // if the two IDs are equal, just return the permalink that WP already built 
        if ($post_blog_id === $real_blog_id) return $categories;
    
        // otherwise, temporary switch to the blog of the post
        // get the right permalink
        // and switch back to the original blog
        switch_to_blog($post_blog_id);
        $categories = get_the_category_list(', ', '', $post->ID);
        switch_to_blog($real_blog_id); 
        
        // dirty trick: posts in the search results that 
        // belongs to blogs other than the one is performing the search
        // get categories always with "comma" separator.
        // the reason is that the right filter is only available 
        // in get_the_category_list function, that gets two more parameters
        // other than the post ID.
        // My opinion is that a filter needs to be added in get_the_category
        // function
        // That will avoid this plugin to override the formatting rules
        // of the active theme
    }
    return $categories;
}

/*
 *  Fix the tag list of the posts that belong to blogs other than the one is
 *  performing the search operation
 */
function gs_fix_post_tags($term_links) {
    
    global $wp_query;
    if (!empty($wp_query->query_vars['s'])) {
    
        global $post;
        global $blog_id;
        
        $post_blog_id = $post->blog_id; // the ID of the blog which the post belongs to
        $real_blog_id = $blog_id;       // the ID of the blog that is performing the search operation
    
        // if the two IDs are equal, just return the permalink that WP already built 
        if ($post_blog_id === $real_blog_id) return $term_links;
    
        // otherwise, temporary switch to the blog of the post
        // get the right permalink
        // and switch back to the original blog
        switch_to_blog($post_blog_id);
        $term_links = get_the_term_list($post->ID, 'post_tag', '', ', ', '');
        switch_to_blog($real_blog_id);
        
        // dirty trick: posts in the search results that 
        // belongs to blogs other than the one is performing the search
        // get tags always with "comma" separator and empty "before" and "after"
        // the reason is that the right filter is only available 
        // in get_the_term_list function, that gets three more parameters
        // other than the post ID.
        // My opinion is that a filter needs to be added in get_the_terms
        // function
        // That will avoid this plugin to override the formatting rules
        // of the active theme

    }
    return $term_links;
}

add_filter('posts_fields', 'gs_replace_fields');
add_filter('posts_request', 'gs_replace_relation');
add_filter('post_link', 'gs_fix_permalinks');
//add_filter('get_edit_post_link', 'gs_fix_get_edit_post_links'); // getting trouble - got fixed in the next version 
add_filter('edit_post_link', 'gs_fix_edit_post_links');
add_filter('the_category', 'gs_fix_post_categories');
add_filter('term_links-post_tag', 'gs_fix_post_tags');
add_filter('the_excerpt', 'gs_augment_search_excerpt');
?>