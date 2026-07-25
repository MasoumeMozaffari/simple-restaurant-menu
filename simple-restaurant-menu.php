<?php
require_once __DIR__ . '/vendor/autoload.php';

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

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
        'public'      => true,
        'has_archive' => true,
        'menu_icon'   => 'dashicons-carrot',
        'supports'    => array('title', 'editor', 'thumbnail'),
    ));
}
add_action('init', 'register_menu_item_post_type');

function register_menu_section_taxonomy() {
    register_taxonomy('menu_section', 'menu_item', array(
        'labels' => array(
            'name'          => 'بخش‌های منو',
            'singular_name' => 'بخش منو',
        ),
        'hierarchical' => true,
        'public'       => true,
        'show_ui'      => true,
    ));
}
add_action('init', 'register_menu_section_taxonomy');

function load_carbon_fields() {
    Carbon_Fields::boot();
}
add_action('after_setup_theme', 'load_carbon_fields');

function create_menu_item_fields() {
    Container::make('post_meta', 'جزئیات آیتم منو')
        ->where('post_type', '=', 'menu_item')
        ->add_fields(array(
            Field::make('text', 'menu_item_price', 'قیمت (تومان)'),

            Field::make('set', 'menu_item_badges', 'نشان‌های رژیمی')
                ->add_options(array(
                    'vegetarian' => 'گیاهی',
                    'nuts'        => 'حاوی آجیل',
                    'dairy'       => 'حاوی لبنیات',
                    'spicy'       => 'تند',
                    'glutenfree'  => 'بدون گلوتن',
                )),
        ));
}
add_action('carbon_fields_register_fields', 'create_menu_item_fields');