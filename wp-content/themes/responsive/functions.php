<?php

// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/**
 *
 * WARNING: Please do not edit this file in any way
 *
 * load the theme function files
 */
require ( get_template_directory() . '/includes/functions.php' );
require ( get_template_directory() . '/includes/theme-options.php' );
require ( get_template_directory() . '/includes/post-custom-meta.php' );
require ( get_template_directory() . '/includes/tha-theme-hooks.php' );
require ( get_template_directory() . '/includes/hooks.php' );
require ( get_template_directory() . '/includes/version.php' );

if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'before_widget' => '<div id="palash">',
        'after_widget' => '</div>',
       
    ));