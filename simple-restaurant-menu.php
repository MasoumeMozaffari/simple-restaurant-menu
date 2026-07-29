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
            Field::make('text', 'menu_item_discount_percent', 'درصد تخفیف (اختیاری، فقط عدد)'),

            Field::make('set', 'menu_item_badges', 'نشان‌های رژیمی')
                ->add_options(array(
                    'vegetarian'  => 'گیاهی',
                    'nuts'        => 'حاوی آجیل',
                    'dairy'       => 'حاوی لبنیات',
                    'spicy'       => 'تند',
                    'gluten_free' => 'بدون گلوتن',
                )),

            Field::make('text', 'menu_item_order_url', 'لینک سفارش این آیتم (اختیاری)'),
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
            Field::make('text', 'menu_order_text', 'متن دکمه سفارش')->set_default_value('سفارش'),
            Field::make('color', 'menu_background_color', 'رنگ پس‌زمینه')->set_default_value('#fdfaf5'),
        ));
}
add_action('carbon_fields_register_fields', 'create_menu_color_settings');

function load_menu_styles() {
    wp_enqueue_style('simple-restaurant-menu-font', 'https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap');
    wp_enqueue_style('simple-restaurant-menu-style', plugins_url('style.css', __FILE__));
    wp_enqueue_script('simple-restaurant-menu-filter', plugins_url('menu-filter.js', __FILE__), array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'load_menu_styles');

function convert_to_persian_digits($string) {
    $persian_digits = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹');
    return str_replace(range(0, 9), $persian_digits, $string);
}

function display_restaurant_menu() {
    ob_start();
    $main_color = carbon_get_theme_option('menu_main_color');
    $breakfast_color = carbon_get_theme_option('menu_breakfast_color');
    $main_dish_color = carbon_get_theme_option('menu_main_dish_color');
    $drink_color = carbon_get_theme_option('menu_drink_color');
    $order_text = carbon_get_theme_option('menu_order_text');
    $bg_color = carbon_get_theme_option('menu_background_color');

    echo '<style>
        .simple-menu-wrapper { background-color: ' . esc_attr($bg_color) . '; }
        .simple-menu-wrapper .price { color: ' . esc_attr($main_color) . '; }
        .simple-menu-wrapper .cat-breakfast { color: ' . esc_attr($breakfast_color) . '; border-bottom-color: ' . esc_attr($breakfast_color) . '; }
        .simple-menu-wrapper .cat-main-dish { color: ' . esc_attr($main_dish_color) . '; border-bottom-color: ' . esc_attr($main_dish_color) . '; }
        .simple-menu-wrapper .cat-drink { color: ' . esc_attr($drink_color) . '; border-bottom-color: ' . esc_attr($drink_color) . '; }
        .simple-menu-wrapper .item-order-btn { background-color: ' . esc_attr($main_color) . '; }
    </style>';

    echo '<div class="simple-menu-wrapper">';

    $sections = get_terms(array(
        'taxonomy' => 'menu_section',
        'hide_empty' => false,
    ));

    echo '<div class="cat-filters">';
    echo '<button class="filter-btn active" data-filter="all">همه</button>';
    foreach ($sections as $s) {
        echo '<button class="filter-btn" data-filter="' . esc_attr($s->slug) . '">' . esc_html($s->name) . '</button>';
    }
    echo '</div>';

    $badge_labels = array(
        'vegetarian'  => 'گیاهی',
        'gluten_free' => 'بدون گلوتن',
        'nuts'        => 'حاوی آجیل',
        'dairy'       => 'حاوی لبنیات',
        'spicy'       => 'تند',
    );

    echo '<div class="items-grid">';

    foreach ($sections as $section) {
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
                $discount_percent = carbon_get_post_meta(get_the_ID(), 'menu_item_discount_percent');
                $badges = carbon_get_post_meta(get_the_ID(), 'menu_item_badges');
                $item_order_url = carbon_get_post_meta(get_the_ID(), 'menu_item_order_url');

                $has_discount = !empty($discount_percent) && is_numeric($discount_percent) && $discount_percent > 0;
                $final_price = $price;
                if ($has_discount) {
                    $final_price = round($price - ($price * $discount_percent / 100));
                }

                echo '<div class="item" data-category="' . esc_attr($section->slug) . '">';
                if (has_post_thumbnail()) {
                    the_post_thumbnail('thumbnail');
                }
                echo '<div class="item-details">';
                echo '<div class="item-info">';
                echo '<h3>' . esc_html(get_the_title()) . '</h3>';
                echo '<p>' . esc_html(get_the_excerpt()) . '</p>';

                foreach ($badges as $badge) {
                    $badge_label = isset($badge_labels[$badge]) ? $badge_labels[$badge] : $badge;
                    echo '<span class="badge badge-' . esc_attr($badge) . '">' . esc_html($badge_label) . '</span>';
                }

                echo '</div>';

                echo '<div class="price-order">';
                if ($has_discount) {
                    echo '<span class="discount-badge">' . esc_html(convert_to_persian_digits($discount_percent)) . '٪</span>';
                    echo '<p class="original-price">' . esc_html(convert_to_persian_digits($price)) . '</p>';
                }
                echo '<p class="price">' . esc_html(convert_to_persian_digits($final_price)) . ' تومان</p>';
                if (!empty($item_order_url)) {
                    echo '<a href="' . esc_url($item_order_url) . '" target="_blank" class="item-order-btn">' . esc_html($order_text) . '</a>';
                }
                echo '</div>';

                echo '</div>';
                echo '</div>';
            }
            wp_reset_postdata();
        }
    }

    echo '</div>';
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('simple_menu', 'display_restaurant_menu');

function register_restaurant_menu_widget($widgets_manager) {
    require_once __DIR__ . '/widget-restaurant-menu.php';
    $widgets_manager->register(new \Restaurant_Menu_Widget());
}
add_action('elementor/widgets/register', 'register_restaurant_menu_widget');