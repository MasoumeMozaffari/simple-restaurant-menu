<?php
/**
 * Plugin Name: Simple Restaurant Menu
 * Description: افزونه‌ای برای نمایش منوی رستوران
 * Version: 1.0
 * Author: Masoume Mozaffari
 */
function register_menu_item_post_type() {
    register_post_type('menu_item', array(
        'labels' => array(
            'name' => 'آیتم‌های منو',
            'singular_name' => 'آیتم منو',
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-carrot',
        'supports' => array('title', 'editor', 'thumbnail'),
    ));
}
add_action('init', 'register_menu_item_post_type');
function register_menu_section_taxonomy() {
    register_taxonomy('menu_section', 'menu_item', array(
        'labels' => array(
            'name' => 'بخش‌های منو',
            'singular_name' => 'بخش منو',
        ),
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
    ));
}
add_action('init', 'register_menu_section_taxonomy');