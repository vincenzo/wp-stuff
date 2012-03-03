<?php
/*
 *  Plugin Name: Signature
 *  Description: Adds the ability for users to build custom and complex signatures for their posts.
 *  Version: 1.0
 *  Author: Vincenzo Russo
 *  Author URI: http://neminis.org
 **/


// Usermeta keys
$usermeta_signature = "user_signature";
$usermeta_signature_type = "user_signature_type";

// Postmeta key
$postmeta_signature = "post_signature";
$postmeta_signature_option = "post_signature_opt";

// XHTML tags allowed in the custom signature
$xhtml_tags_allowed = "<p>,<strong>,<em>,<a>";

// Directory where this plugin is installed
$dir_signature = dirname(__FILE__);

// Include admin functions
require $dir_signature . "/inc/signature.admin.php";

/**
 *  Return the signature of a post.
 *
 * @param  int $post_id
 *  The post id
 * @return
 *  The signature
 **/
function sig_get_post_signature($post_id) {
  global $postmeta_signature;
  // Get the current signature attached to the post
  $signature = get_post_meta($post_id, $postmeta_signature, true);

  // Get the logged-in user's ID
  $current_uid = get_current_user_id();
  // Get their signature
  $user_signature = sig_get_user_signature($current_uid);

  // If the user has updated their signature since the post was last saved,
  // update the post signature to match the new user's one
  if ($user_signature != $signature) {
    return sig_add_signature_as_postmeta($post_id, $user_signature);
  }

  // Return the up to date signature
  return $signature;
}

/**
 * Get a user's signature
 * 
 * @param int $user_id
 *  User ID
 * @return string
 *  The signature
 */
function sig_get_user_signature($user_id) {
  global $usermeta_signature, $usermeta_signature_type;

  $sig_type = get_user_meta($user_id, $usermeta_signature_type, TRUE);
  $user = get_userdata($user_id);
  $signature = "";

  // Set the desired signature
  switch ($sig_type) {
    case "first_name":
    case "last_name":
    case "nickname":
      $signature = $user->$sig_type;
      break;
    case "first_name_l":
      $signature = $user->first_name . " " . $user->last_name[0] . ".";
      break;
    case "f_last_name":
      $signature = $user->first_name[0] . ". " . $user->last_name;
      break;
    case "fullname":
      $signature = $user->first_name . " " . $user->last_name;
      break;
    case "anonymous":
      $signature = __('Anonymous', 'signature');
      break;
    case "open":
      $signature = get_user_meta($user_id, $usermeta_signature, TRUE);
      $search = array(
        "[first]",
        "[last]",
        "[nick]",
        "[email]",
        "[www]",
      );
      $replace = array(
        $user->first_name,
        $user->last_name,
        $user->nickname,
        $user->user_email,
        $user->user_url,
      );
      $signature = str_replace($search, $replace, $signature);
      break;
    case "off":
    default:
      ;
  }
  return $signature;
}


/**
 * Add the signature to a post.
 *
 * @param int $pid
 *  The post ID
 **/
function sig_add_signature_as_postmeta($pid, $previous_signature = NULL) {
  global $postmeta_signature, $postmeta_signature_option;

  $post = wp_get_single_post($pid, OBJECT);
  $author_id = $post->post_author;
  $signature = sig_get_user_signature($author_id);

  // Getting the value for the signature per-post option
  $signature_post = $_POST['signature-options'] ? $_POST['signature-options'] : get_post_meta($post->ID, $postmeta_signature_option, TRUE);
  update_post_meta($post->ID, $postmeta_signature_option, $signature_post);

  // If signature is OFF, delete it from the postmeta values and return
  if ($signature_post == 'off') {
    delete_post_meta($post->ID,$postmeta_signature);
    return '';
  }

  // Save only if different from previous signature
  if ($signature != $previous_signature) {
    global $xhtml_tags_allowed;
    $signature = strip_tags($signature, $xhtml_tags_allowed);
    update_post_meta($post->ID, $postmeta_signature, $signature);
  }

  return $signature;
}

/**
 * Append the signature at the bottom of the post content
 *
 * @param string $content
 *  The original content of the post
 * @return string
 *  The updated content
 */
function sig_attach_signature_to_post_content($content) {
  global $post;
  $signature = sig_get_post_signature($post->ID);
  return $content . '<p id="signature">' . $signature . '</p>';
}

/**
 *  Handler executed on the plugin activation.
 *  It set up the default settings (signature off).
 */
function sig_on_activate() {
  global $userdata, $usermeta_signature, $usermeta_signature_type, $usermeta_signature_excerpt;
  get_currentuserinfo();

  $metas = get_user_meta($userdata->ID, "user_signature_type", TRUE);

  // Never activated before or usermeta manually deleted from DB
  if ($metas == '') {
    update_user_meta($userdata->ID, $usermeta_signature, __('Example of custom signature. You can add your fullname by using the special code [first] [last]. Other special codes are available: see below.', 'signature'));
    update_user_meta($userdata->ID, $usermeta_signature_type, 'off');
    update_user_meta($userdata->ID, $usermeta_signature_excerpt, 'off');
  }
}


/**
 *  Handler executed on the plugin initialization.
 *  It loads the localization.
 */
function sig_on_init() {
  // use load_textdomain instead of load_plugin_textdomain for two reasons:
  // 1. this makes simpler to be independent from the plugin location (pluings or mu-plugins)
  // 2. prevent issues due to upcoming 2.7 version (where load_plugin_textdomain will change)
  load_textdomain("signature", trailingslashit(dirname(__FILE__)) . "signature-lang/signature-" . get_locale() . '.mo');
}

register_activation_hook(__FILE__, 'sig_on_activate');

add_action('init', 'sig_on_init');
add_action('admin_menu', 'sig_attach_admin_menus');
add_action('admin_menu', 'sig_options_post_box');
add_action('xmlrpc_publish_post', 'sig_add_signature_as_postmeta');
add_action('save_post', 'sig_add_signature_as_postmeta');
add_action('publish_post', 'sig_add_signature_as_postmeta');
add_action('publish_phone', 'sig_add_signature_as_postmeta');
add_action('publish_page', 'sig_add_signature_as_postmeta');
add_action('private_to_publish', 'sig_add_signature_as_postmeta');
add_filter('the_content', 'sig_attach_signature_to_post_content');
