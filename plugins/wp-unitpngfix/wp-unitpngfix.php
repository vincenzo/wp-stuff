<?php
/*
   Plugin Name: Wp-UnitPNGfix
   Plugin URI: http://neminis.org/software/wordpress/plugin-wp-unitpngfix/
   Description: This plugin includes the 'unitpngfix.js' javascript file if the browser is IE6 or lower. In plain words, it implements the solution for the PNG trasparency provided by Unit Interactive Labs (http://labs.unitinteractive.com/unitpngfix.php). It works on img objects and background-image attributes.
   Version: 0.2.1
   Author: Vincenzo Russo (aka Nemo)
   Author URI: http://neminis.org/
  
   This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Library General Public License for more details.
   You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA. 
*/ 

function wp_pngfix_echo() {
    $workdir = get_bloginfo('wpurl') . "/" . basename(WP_CONTENT_DIR) . "/plugins/wp-unitpngfix";
    echo "<!--[if lt IE 7.]>";
    echo "<script defer type='text/javascript' src='" . $workdir . "/unitpngfix/unitpngfix.js'></script>";
    echo "<![endif]-->\n";
}

add_action('wp_head', 'wp_pngfix_echo');