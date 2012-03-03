<?php // Do not delete these lines
if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME'])) die ('Please do not load this page directly. Thanks!');
if (!empty($post->post_password)) { // if there's a password
	if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
?>

<h2><?php _e('Password protected', 'idea_domain'); ?></h2>
<p><?php _e('Enter the password to view comments.', 'idea_domain'); ?></p>

<?php return;
	}
}

	/* This variable is for alternating comment background */

$oddcomment = 'alt';

?>

<?php if (comments_open()) : ?>
<h3 id="comments"><?php comments_number(__('No Comments', 'idea_domain'), __('One Comment', 'idea_domain'), __('% Comments', 'idea_domain') );?></h3>
<?php endif;?>

<?php if ($comments) : ?>
	
<ol class="commentlist">
<?php foreach ($comments as $comment) : ?>

	<li class="<?php echo $oddcomment; ?>" id="comment-<?php comment_ID() ?>">

<div class="commentmetadata">
<strong>
        
        <?php 
            $auth_url = "";
            ob_start();
            comment_author_url();
            $auth_url = ob_get_contents();
            ob_end_clean();
            
            $auth = "";
            ob_start();
            comment_author();
            $auth = ob_get_contents();
            ob_end_clean();
            
            $auth_link = "<em>Nessuna homepage o blog</em>";
            if ($auth_url != "") {
               $auth_link = '<a rel="nofollow" href="' . $auth_url . '" title="' . $auth . '">' . $auth_url . '</a>';
            }
        ?>
        
        <?php comment_author();?></strong> &rarr; <?php echo $auth_link; ?>
        
 		<?php if ($comment->comment_approved == '0') : ?>
		<em><?php _e('Your comment is awaiting moderation.', 'idea_domain'); ?></em>
 		<?php endif; ?>
</div>

<?php comment_text() ?>
<div class="commentmetadata-foot">
<small><?php comment_date('d M \'y') ?>, <?php comment_time('H:i') ?> &mdash; <a href="#comment-<?php comment_ID() ?>" title="<?php _e('Permanent link to this comment', 'idea_domain');?>" class="comment-permalink">permalink</a> <?php edit_comment_link('&ndash; e','',''); ?></small>
</div>
	</li>

<?php /* Changes every other comment to a different class */
	if ('alt' == $oddcomment) $oddcomment = '';
	else $oddcomment = 'alt';
?>

<?php endforeach; /* end for each comment */ ?>
	</ol>

<?php else : // this is displayed if there are no comments so far ?>

<?php if ('open' == $post->comment_status) : ?>

	<?php else : // comments are closed ?>

<p class="nocomments">&nbsp;</p>

	<?php endif; ?>
<?php endif; ?>


<?php if ('open' == $post->comment_status) : ?>

		<h3 id="respond"><?php _e('Leave a Reply', 'idea_domain') ?></h3>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p><?php _e('You must be', 'idea_domain'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>"><?php _e('logged in', 'idea_domain'); ?></a> <?php _e('to post a comment.', 'idea_domain'); ?></p>

<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">
<?php if ( $user_ID ) : ?>

<p><?php _e('Logged in as', 'idea_domain') ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account', 'idea_domain');?>"><?php _e('Logout', 'idea_domain');?> &raquo;</a></p>

<?php else : ?>

<p><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="40" tabindex="1" />
<label for="author"><small><?php _e('Name', 'idea_domain');?> <?php if ($req) echo "(" . __('required', 'idea_domain') . ")"; ?></small></label></p>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="40" tabindex="2" />
<label for="email"><small><?php _e('E-Mail', 'idea_domain');?> <?php if ($req) echo "(" . __('required, but will not published', 'idea_domain') . ")"; ?></small></label></p>

<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="40" tabindex="3" />
<label for="url"><small><?php _e('Website', 'idea_domain'); ?></small></label></p>

<?php endif; ?>

<?php if(function_exists('lmbbox_comment_quicktags_display')) {
lmbbox_comment_quicktags_display(); } ?>
<!--<p><small><strong>XHTML:</strong> <?php _e('You can use these tags&#58;'); ?> <?php echo allowed_tags(); ?></small></p>-->

<p><textarea name="comment" id="comment" cols="60" rows="10" tabindex="4"></textarea></p>

<p><input name="submit" type="submit" id="submit" tabindex="5" value="<?php _e('Submit Comment', 'idea_domain'); ?>" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>

<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>

<?php endif; // if you delete this the sky will fall on your head ?>