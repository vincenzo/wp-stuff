<?php

function smart_footnotes_shortcode_handler($atts, $content = null) {
    $default_atts = array(
        'text' => 'null'
    );

    extract(shortcode_atts($default_atts, $atts));
}