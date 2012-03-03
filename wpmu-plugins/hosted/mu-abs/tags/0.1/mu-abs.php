<?php 
/*
 * Plugin Name: Automatic Blog Subscription
 * Plugin URI: http://neminis.org/software/wordpress/mu-automatic-blog-subscription/
 * Description: When a logged in user visits a blog for which they have no capabilities (i.e. not subscriber), they are
 * automatically added as a subscriber.
 * Version: 0.1
 * Author: Vincenzo Russo (aka Nemo)
 * Author URI: http://neminis.org/
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Library General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
 **/
 
function abs_autosubscribe() {
    global $current_site, $current_blog, $current_user, $user_ID, $wpdb;
    
    // not subscribed to current blog and not site admin
    if (!is_blog_user() || !is_site_admin()) { 
        add_user_to_blog($current_blog->blog_id, $user_ID, 'subscriber');
    }
}

add_action('get_header', 'abs_autosubscribe');
?>