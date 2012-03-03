<?php // Do not delete these lines
if ('related-posts.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');
if (!empty($post->post_password)) { // if there's a password
	if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
?>

<h2><?php _e('Password protected', 'idea_domain'); ?></h2>
<p><?php _e('Enter the password to view comments.', 'idea_domain'); ?></p>

<?php return;
	}
}

	/* This variable is for alternating comment background */

$oddrelated = 'alt';

?>
<?php if(function_exists('similar_posts')) { ?>

<h3 id="r-posts"><?php _e('Similar posts', 'idea_domain'); ?></h3>

<?php similar_posts();} ?>