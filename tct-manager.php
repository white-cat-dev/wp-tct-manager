<?php
/*
 * Plugin Name: TCT manager
 * Description: Integration with the production management panel
 * Version: 0.1
 */

require __DIR__ . '/functions.php';

add_action('wp_enqueue_scripts', 'tct_manager_enqueue_scripts');
// add_filter('page_template', 'tct_page_template');
add_action('wp_insert_post_data', 'tct_manager_insert_post_data');
add_filter('the_content', 'tct_manager_content');
