<?php if ( !empty($post->post_password) && $_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) : ?>
<p><?php _e('Enter your password to view comments.', 'keepitsimple_domain'); ?></p>
<?php return; endif; ?>

<div class="comments" id="comments">
<h2><?php _e('Comments', 'keepitsimple_domain')?></h2>
<?php if ( $comments ) : ?>
<?php $i = 1; $trackbacks = 0; ?>
<?php foreach ($comments as $comment) : ?>
<?php if(get_comment_type() == 'comment') { ?>
<?php 
  $isByAuthor = false;
  if($comment->comment_author_email == get_the_author_email()) {
    $isByAuthor = true;
  }
?>     

<div id="comment-<?php comment_ID() ?>" class="comment-container <?php if($isByAuthor ) { echo 'owner';} ?>">
  <div class="comment-meta">
    <?php echo get_avatar( $comment, 48,  get_bloginfo('template_url').'/img/no-avatar.gif' ); ?>
  </div>
  <div class="comment-content">
    <h3><?php echo simple_comment_author(); ?></h3>
    <span><?php comment_date('d.m.y') ?></span>
    <?php comment_text() ?>
  </div>
  <div class="clear"></div>
</div>
<?php } else { $trackbacks++; }?>
<?php endforeach; ?>

<?php if($trackbacks) { ?>
<div class="clear">&nbsp;</div>
<h2><?php _e('Trackbacks', 'keepitsimple_domain')?></h2>
<ol id="noncomments">
<?php foreach ($comments as $comment) : ?>
<?php if(get_comment_type() != 'comment') { ?>
<li><?php comment_author_link() ?></li>
<?php } ?>
<?php endforeach; ?>
</ol>
<?php } ?>

<?php else : // If there are no comments yet ?>
	<p><?php _e('No comments yet.', 'keepitsimple_domain'); ?></p>
<?php endif; ?>

<?php if ( comments_open() ) : ?>      
      <h2 id="respond"><?php _e('Leave a comment', 'keepitsimple_domain')?></h2>
<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p><?php printf(__('You must be <a href="%s">logged in</a> to post a comment.', 'keepitsimple_domain'), get_option('siteurl')."/wp-login.php?redirect_to=".urlencode(get_permalink()));?></p>
<?php else : ?>
      
<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="form-comment">      
<?php if ( $user_ID ) : ?>

<p><?php printf(__('Logged in as %s.', 'keepitsimple_domain'), '<a href="'.get_option('siteurl').'/wp-admin/profile.php">'.$user_identity.'</a>'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account', 'keepitsimple_domain') ?>"><?php _e('Log out &raquo;', 'keepitsimple_domain'); ?></a></p>

<?php else : ?>      
      <div><input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" /><label for="author"><?php _e('Nickname', 'keepitsimple_domain') ?> *</label></div>
      <div><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" /><label for="email"><?php _e('E-mail', 'keepitsimple_domain') ?> *</label></div>
      <div><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" /><label for="url"><?php _e('Url', 'keepitsimple_domain')?></label></div>
<?php endif; ?>
      <p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>
      <button type="submit" name="submit"><?php _e('Say it', 'keepitsimple_domain') ?></button>
      <p><input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" /></p>
<?php do_action('comment_form', $post->ID); ?>
</form>

<?php endif; // If registration required and not logged in ?>

<?php else : // Comments are closed ?>
<p><?php _e('Sorry, the comment form is closed at this time.', 'keepitsimple_domain'); ?></p>
<?php endif; ?>
</div>