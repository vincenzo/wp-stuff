<?php
/*
Plugin Name: FT Signature Manager
Plugin URI: http://fullthrottledevelopment.com/signature_manager
Description: FT Signature Manager allows each author on your blog to include a signature at the end of their posts.
Version: 1.3
Author: FullThrottle Development
Author URI: http://fullthrottledevelopment.com/
*/

/*Copyright 2008 FullThrottle Development (http://fullthrottledevelopment.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Release History
 1.0 - Initial Release
 1.1 - Fixed a bug that prevented writing / editing pages.
 1.2 - Moved Signature Management to profile page.
 1.3 - Fixed a bug that caused signatures not to show up on certain server configs.
*/


define( 'FT_Signature_Manager_Vesion' , '1.3' );

// Setup form security
if ( !function_exists('wp_nonce_field') ) {
    function ft_signature_manager_nonce_field($action = -1) { return; }
    $ft_signature_manager_nonce = -1;
} else {
	if( !function_exists( 'ft_signature_manager_nonce_field' ) ) {
	function ft_signature_manager_nonce_field($action = -1,$name = 'ft_signature_manager-update-checkers') { return wp_nonce_field($action,$name); }
	define('FT_Signature_Manager_NONCE' , 'ft_signature_manager-update-checkers');
	}
}

//Add admin page links and call page
function ft_signature_manager_admin_link() {
	add_submenu_page('users.php', 'Signature Options', 'Signature Options', 1, basename(__FILE__), 'ft_signature_manager_admin_page');
}

//This function contains the content for the admin page
function ft_signature_manager_admin_page(){
	global $wpdb;
	//process data if sumbitted
	if ( isset($_POST['ft_signature_manager_update']) && $_POST['ft_signature_manager_update'] == 'update' ){
		check_admin_referer( '$ft_signature_manager_nonce', FT_Signature_Manager_NONCE );	
		if ( isset($_POST['ft_signature_01']) && !empty($_POST['ft_signature_01']) ){
			if ( update_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01' , $wpdb->prepare($_POST['ft_signature_01'] ) ) ){
				$current_signature = $wpdb->prepare( $_POST['ft_signature_01'] );
			}
		}else{
			delete_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01' , $wpdb->prepare($_POST['ft_signature_01'] ) );
		}
		
		if ( isset($_POST['ft_signature_01_default']) && !empty($_POST['ft_signature_01_default']) ){
			if ( update_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01_default' , $wpdb->prepare($_POST['ft_signature_01_default'] ) ) ){
				$current_default = $wpdb->prepare( $_POST['ft_signature_01_default'] );
			}
		}else{
			delete_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01_default' , $wpdb->prepare($_POST['ft_signature_01_default'] ) );
			$current_default = '';
		}
	}

	if ( !isset($current_signature) ){ $current_signature = get_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01' ); }
	if ( !isset($current_default) ) { $current_default = get_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01_default' ); }
	
	?>
	<div class="wrap">
		<h2>Signature Options</h2>
		<p>Enter your post signature in the text area below. HTML markup is allowed.</p>
		<form name="ft_signature_manager" id="ft_signature_manger" action="" method="post">
			<?php ft_signature_manager_nonce_field('$ft_signature_manager_nonce', FT_Signature_Manager_NONCE);?>
			<input type="hidden" name="ft_signature_manager_update" value="update" />
			<textarea cols="75" rows="5" name="ft_signature_01"><?php echo stripslashes( $current_signature ); ?></textarea><br />
			<p>Will the signature be on or off by default?<br />
			<input type="radio" name="ft_signature_01_default" value="yes" <?php echo ft_signature_manager_default_setting( 'yes' );?> /> On<br />
			<input type="radio" name="ft_signature_01_default" value="no" <?php echo ft_signature_manager_default_setting( 'no' );?> /> Off<br />
			</p>
			<p class="submit">
				<input type="submit" name="submit" value="<?php _e('Update');?>" />
			</p>
		</form>
		<?php if ( get_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01' ) ){ ?>
		<h2 style="margin:20px 0;">Preview</h2>
		<div><?php echo get_usermeta( ft_signature_manager_current_userID() , 'ft_signature_01' );?></div>
		<?php } ?>
	</div>
	<?php
}

//This function grabs the current user's ID
function ft_signature_manager_current_userID(){
	global $userdata;
	return $userdata->ID;
}


//This function determines if the specified passed value is equal to the value in the DB and returns 'checked' if it is.
function ft_signature_manager_default_setting( $value ){
	if ( !empty($value) ){
		if ( get_usermeta( ft_signature_manager_current_userID() , "ft_signature_01_default" ) == $value ){
			echo 'checked';
		}else{
			echo '';
		}
	}
}

//This function adds the signature meta baox to the Write Post Screen
function ft_signature_manager_add_post_box() {
	add_meta_box(
        'ft_signature_manager_options',
        'Signature Options', 
         'ft_signature_manager_post_box_content',
        'post' 
    );
}

//This function holds the conent for the signature box
function ft_signature_manager_post_box_content() {
	ft_signature_manager_nonce_field('$ft_signature_manager_nonce', FT_Signature_Manager_NONCE);

	if( !isset( $id_['post'] ) ) {
		$mypost = $_GET['post'];
		$value = get_post_meta( $mypost, 'ft_signature_manager', true );
	}

	if ( !isset($value) || $value == '' || empty($value) ){
		$value = get_usermeta( ft_signature_manager_current_userID() , "ft_signature_01_default" );
	}
	
	?>
	<div>
		<p>Include Signature at end of this post?<br />
		<input type="radio" name="ft_signature_manager" value="yes" <?php if ( $value == 'yes' ){ echo 'checked';} ?> /> Yes<br />
		<input type="radio" name="ft_signature_manager" value="no" <?php if ( $value == 'no' || $value == '' ){ echo 'checked';} ?> /> No
		</p>
	</div>
	<?php
}

//This function saves the content from my event box
function ft_signature_manager_save_meta_box() {
	global $wpdb;

	//fire off if we're on the post screen
	if ( $_POST['post_type'] == 'post' ){
		//form security
		check_admin_referer( '$ft_signature_manager_nonce', FT_Signature_Manager_NONCE );

		//current post ID
		if( !isset( $id ) )
	      $id = $_REQUEST[ 'post_ID' ];

	    //make sure user has permission to edit
	    if( !current_user_can('edit_post', $id) )
	        return $id;

		//if id is set, update postmeta, otherwise, delete any existing postmeta.
		if ( isset($_POST['ft_signature_manager'] ) && $_POST['ft_signature_manager'] != '' ){
			if ( get_post_meta( $id, 'ft_signature_manager') ){
				update_post_meta( $id , 'ft_signature_manager' , $wpdb->prepare($_POST['ft_signature_manager'] ) );
			}else{
				add_post_meta( $id , 'ft_signature_manager' , $wpdb->prepare($_POST['ft_signature_manager'] ) );
			}
		}else{
			delete_post_meta( $id, 'ft_signature_manager' );
		}
	}
}

//This function adds the signature to the end of the post if set on write-post page
function ft_signature_manager_add_signature( $content ){
	global $post;
	
	if ( isset($post->ID) ){
		$mypost = $post->ID;
		
		if ( get_post_meta( $mypost , 'ft_signature_manager' , true ) == 'yes' ){
			$signature = get_usermeta( $post->post_author , 'ft_signature_01' );
			$sig = $content.$signature;
			return $sig;
		}
		
		return $content;
	}else{
		return $content;
	}
}


add_action('admin_menu' , 'ft_signature_manager_admin_link' );

add_action( 'admin_menu', 'ft_signature_manager_add_post_box' );
add_action( 'publish_post', 'ft_signature_manager_save_meta_box' );
add_action( 'write_post', 'ft_signature_manager_save_meta_box' );
add_action( 'edit_post', 'ft_signature_manager_save_meta_box' );

add_filter( 'the_content' , 'ft_signature_manager_add_signature' );
?>