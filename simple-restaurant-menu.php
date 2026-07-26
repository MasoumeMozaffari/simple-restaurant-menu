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
                    'vegetarian'  => 'گیاهی',
                    'nuts'        => 'حاوی آجیل',
                    'dairy'       => 'حاوی لبنیات',
                    'spicy'       => 'تند',
                    'gluten_free' => 'بدون گلوتن',
                )),
        ));
}
add_action('carbon_fields_register_fields', 'create_menu_item_fields');

function create_menu_color_settings() {
    Container::make('theme_options', 'تنظیمات رنگ منو')
        ->add_fields(array(
            Field::make('color', 'menu_main_color', 'رنگ اصلی')->set_default_value('#b8860b'),
            Field::make('color', 'menu_breakfast_color', 'رنگ صبحانه')->set_default_value('#e67e22'),
            Field::make('color', 'menu_main_dish_color', 'رنگ غذای اصلی')->set_default_value('#c0392b'),
            Field::make('color', 'menu_drink_color', 'رنگ نوشیدنی')->set_default_value('#2980b9'),
        ));
}
add_action('carbon_fields_register_fields', 'create_menu_color_settings');

function load_menu_styles() {
    wp_enqueue_style('simple-restaurant-menu-font', 'https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap');
    wp_enqueue_style('simple-restaurant-menu-style', plugins_url('style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'load_menu_styles');

function display_restaurant_menu() {
    ob_start();
    $main_color = carbon_get_theme_option('menu_main_color');
    $breakfast_color = carbon_get_theme_option('menu_breakfast_color');
    $main_dish_color = carbon_get_theme_option('menu_main_dish_color');
    $drink_color = carbon_get_theme_option('menu_drink_color');

    echo '<style>
        .simple-menu-wrapper h1 { color: ' . esc_attr($main_color) . '; }
        .simple-menu-wrapper .price { color: ' . esc_attr($main_color) . '; }
        .simple-menu-wrapper .cat-breakfast { color: ' . esc_attr($breakfast_color) . '; border-bottom-color: ' . esc_attr($breakfast_color) . '; }
        .simple-menu-wrapper .cat-main-dish { color: ' . esc_attr($main_dish_color) . '; border-bottom-color: ' . esc_attr($main_dish_color) . '; }
        .simple-menu-wrapper .cat-drink { color: ' . esc_attr($drink_color) . '; border-bottom-color: ' . esc_attr($drink_color) . '; }
    </style>';

    echo '<div class="simple-menu-wrapper">';

    $badge_labels = array(
        'vegetarian'  => 'گیاهی',
        'gluten_free' => 'بدون گلوتن',
        'nuts'        => 'حاوی آجیل',
        'dairy'       => 'حاوی لبنیات',
        'spicy'       => 'تند',
    );

    $sections = get_terms(array(
        'taxonomy' => 'menu_section',
        'hide_empty' => false,
    ));

    echo '<h1>رستوران فرشته</h1>';
    echo '<p class="subtitle">منو امروز</p>';

    foreach ($sections as $section) {
        echo '<h2 class="cat-' . esc_attr($section->slug) . '">' . esc_html($section->name) . '</h2>';

        $items_query = new WP_Query(array(
            'post_type' => 'menu_item',
            'tax_query' => array(
                array(
                    'taxonomy' => 'menu_section',
                    'field' => 'slug',
                    'terms' => $section->slug,
                ),
            ),
        ));

        if ($items_query->have_posts()) {
            while ($items_query->have_posts()) {
                $items_query->the_post();

                $price = carbon_get_post_meta(get_the_ID(), 'menu_item_price');
                $badges = carbon_get_post_meta(get_the_ID(), 'menu_item_badges');

                echo '<div class="item">';
                if (has_post_thumbnail()) {
                    the_post_thumbnail('thumbnail');
                }
                echo '<div class="item-details">';
                echo '<div>';
                echo '<h3>' . esc_html(get_the_title()) . '</h3>';
                echo '<p>' . esc_html(get_the_excerpt()) . '</p>';

                foreach ($badges as $badge) {
                    $badge_label = isset($badge_labels[$badge]) ? $badge_labels[$badge] : $badge;
                    echo '<span class="badge badge-' . esc_attr($badge) . '">' . esc_html($badge_label) . '</span>';
                }

                echo '</div>';
                echo '<p class="price">' . esc_html($price) . ' تومان</p>';
                echo '</div>';
                echo '</div>';
            }
            wp_reset_postdata();
        }
    }

    echo '</div>';
    return ob_get_clean();
}
add_shortcode('simple_menu', 'display_restaurant_menu');

function register_restaurant_menu_widget($widgets_manager) {
    require_once __DIR__ . '/widget-restaurant-menu.php';
    $widgets_manager->register(new \Restaurant_Menu_Widget());
}
add_action('elementor/widgets/register', 'register_restaurant_menu_widget');