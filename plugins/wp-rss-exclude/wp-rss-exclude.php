<?php

/**
 * Plugin Name: WP RSS Exclude
 * Plugin URI: https://github.com/vincenzo/wp-rss-exclude
 * Description: Exclude a category from WordPress main RSS feed.
 * Version: 0.1
 * Author: Vincenzo Russo
 * Author URI: http://artetecha.com
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Library General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 ***/

/** 
* Hooks the WP pre_get_posts action to remove posts in particular categories  
* from the feed, by interfering with the WP Query object. 
* 
* @param object $wp_query The WP Query object  
* @return void 
**/
function sw_pre_get_posts( $wp_query ) {    
    // Only for feeds    
    if ( ! is_feed() )        
        return;    
     
    // Restrict to the main feed...    
     
    // ...NOT category feeds    
    if ( $wp_query->query_vars[ 'category_name' ] )        
        return;    
     
    // ...NOT comment feeds    
    if ( $wp_query->query_vars[ 'withcomments' ] )        
        return;    
     
    // ...NOT tag feeds    
    if ( $wp_query->query_vars[ 'tag' ] )        
        return;    $ex_cats = array();    
     
    // Exclude category    
    $cat = get_term_by( 'slug', 'verso-del-giorno', 'category' );    
    $ex_cats[] = $cat->term_id;    
    // Because the WP Query object is passed by reference, we     
    // can just act on it here without needing to return.    
    set_query_var( 'category__not_in', $ex_cats );
}
add_action( 'pre_get_posts', 'sw_pre_get_posts' );
