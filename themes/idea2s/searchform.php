<form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
<div><input type="text" value="<?php the_search_query(); ?>" name="s" id="s" size="15" />
<!-- input type="submit" id="searchsubmit" value="<?php _e('Search', 'idea_domain');?>" />-->
</div>
</form>