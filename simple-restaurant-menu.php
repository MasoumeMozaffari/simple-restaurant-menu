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
            // فیلدهای قیمت
            Field::make('text', 'menu_item_price', 'قیمت (تومان)'),
            Field::make('text', 'menu_item_discount_percent', 'درصد تخفیف (اختیاری، فقط عدد)'),
            
            // بازه قیمتی
            Field::make('select', 'menu_item_price_range', 'بازه قیمتی')
                ->add_options(array(
                    'economic' => '💰 اقتصادی',
                    'medium' => '💳 متوسط',
                    'expensive' => '💎 گران',
                ))
                ->set_default_value('medium'),
            
            // ✅ وضعیت موجودی (جدید)
            Field::make('select', 'menu_item_stock', 'وضعیت موجودی')
                ->add_options(array(
                    'available' => '✅ موجود',
                    'limited' => '⚠️ تعداد محدود',
                    'unavailable' => '❌ ناموجود',
                    'preorder' => '📦 پیش‌سفارش',
                ))
                ->set_default_value('available'),
            
            // فیلدهای تحویل و ارسال
            Field::make('checkbox', 'menu_item_delivery', 'ارسال با پیک'),
            Field::make('checkbox', 'menu_item_pickup', 'تحویل حضوری'),
            
            // نشان‌های رژیمی
            Field::make('set', 'menu_item_badges', 'نشان‌های رژیمی')
                ->add_options(array(
                    'vegetarian'  => 'گیاهی',
                    'nuts'        => 'حاوی آجیل',
                    'dairy'       => 'حاوی لبنیات',
                    'spicy'       => 'تند',
                    'gluten_free' => 'بدون گلوتن',
                )),
            
            // برچسب‌های ویژه
            Field::make('set', 'menu_item_special_badges', 'برچسب‌های ویژه')
                ->add_options(array(
                    'best_seller' => 'پرفروش‌ترین',
                    'chef_special' => 'ویژه سرآشپز',
                    'new' => 'جدید',
                    'limited' => 'تعداد محدود',
                    'healthy' => 'سالم',
                    'popular' => 'محبوب',
                )),
            
            // امتیاز
            Field::make('select', 'menu_item_rating', 'امتیاز')
                ->add_options(array(
                    '0' => 'بدون امتیاز',
                    '1' => '⭐ ۱',
                    '2' => '⭐⭐ ۲',
                    '3' => '⭐⭐⭐ ۳',
                    '4' => '⭐⭐⭐⭐ ۴',
                    '5' => '⭐⭐⭐⭐⭐ ۵',
                ))
                ->set_default_value('0'),
            
            // زمان آماده‌سازی
            Field::make('text', 'menu_item_prep_time', 'زمان آماده‌سازی (دقیقه)')
                ->set_help_text('مثلاً: ۲۰-۳۰ دقیقه'),
            
            // فیلدهای کوپن و جایزه
            Field::make('checkbox', 'menu_item_has_coupon', 'دارای کوپن تخفیف'),
            Field::make('text', 'menu_item_coupon_code', 'کد کوپن'),
            Field::make('text', 'menu_item_coupon_title', 'عنوان کوپن'),
            Field::make('text', 'menu_item_gift', 'هدیه خرید (مثلاً سوپ رایگان)'),
            
            // لینک سفارش
            Field::make('text', 'menu_item_order_url', 'لینک سفارش این آیتم (اختیاری)'),
        ));
}
add_action('carbon_fields_register_fields', 'create_menu_item_fields');

function create_menu_color_settings() {
    Container::make('theme_options', 'تنظیمات منو')
        ->add_fields(array(
            // رنگ‌بندی
            Field::make('color', 'menu_main_color', 'رنگ اصلی')->set_default_value('#b8860b'),
            Field::make('color', 'menu_breakfast_color', 'رنگ صبحانه')->set_default_value('#e67e22'),
            Field::make('color', 'menu_main_dish_color', 'رنگ غذای اصلی')->set_default_value('#c0392b'),
            Field::make('color', 'menu_drink_color', 'رنگ نوشیدنی')->set_default_value('#2980b9'),
            Field::make('color', 'menu_background_color', 'رنگ پس‌زمینه')->set_default_value('#fdfaf5'),
            
            // تنظیمات دکمه سفارش
            Field::make('text', 'menu_order_text', 'متن دکمه سفارش')->set_default_value('سفارش'),
            Field::make('color', 'menu_order_btn_color', 'رنگ دکمه سفارش')->set_default_value('#2e7d32'),
            Field::make('color', 'menu_order_btn_hover', 'رنگ دکمه در هاور')->set_default_value('#1b5e20'),
            Field::make('color', 'menu_order_btn_text', 'رنگ متن دکمه')->set_default_value('#ffffff'),
            
            // انتخاب فونت
            Field::make('select', 'menu_font_type', 'نوع فونت')
                ->add_options(array(
                    'default' => 'پیش‌فرض (وزیرمتن)',
                    'iransans' => 'ایران‌سنس',
                    'shabnam' => 'شبنم',
                    'sahel' => 'ساحل',
                    'custom' => 'فونت دلخواه (آپلود)',
                ))
                ->set_default_value('default'),
            
            // فیلد آپلود فایل فونت
            Field::make('file', 'menu_custom_font', 'آپلود فایل فونت')
                ->set_type(array('font/ttf', 'font/otf', 'font/woff', 'font/woff2'))
                ->set_help_text('فایل‌های با فرمت‌های ttf, otf, woff, woff2'),
            
            // نام فونت دلخواه
            Field::make('text', 'menu_custom_font_name', 'نام فونت دلخواه')
                ->set_help_text('مثلاً: MyCustomFont'),
        ));
}
add_action('carbon_fields_register_fields', 'create_menu_color_settings');

function load_menu_styles() {
    wp_enqueue_style('simple-restaurant-menu-font', 'https://fonts.googleapis.com/css2?family=Vazirmatn&display=swap');
    wp_enqueue_style('simple-restaurant-menu-style', plugins_url('style.css', __FILE__));
    wp_enqueue_script('simple-restaurant-menu-filter', plugins_url('menu-filter.js', __FILE__), array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'load_menu_styles');

function load_custom_font_styles() {
    $font_type = carbon_get_theme_option('menu_font_type');
    
    if ($font_type === 'custom') {
        $custom_font_id = carbon_get_theme_option('menu_custom_font');
        $custom_font_name = carbon_get_theme_option('menu_custom_font_name');
        
        if (!empty($custom_font_id) && !empty($custom_font_name)) {
            $font_url = wp_get_attachment_url($custom_font_id);
            if ($font_url) {
                add_action('wp_head', function() use ($font_url, $custom_font_name) {
                    echo '<style>
                        @font-face {
                            font-family: "' . esc_attr($custom_font_name) . '";
                            src: url("' . esc_url($font_url) . '") format("truetype");
                            font-weight: normal;
                            font-style: normal;
                            font-display: swap;
                        }
                    </style>';
                });
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'load_custom_font_styles');

function allow_font_mime_types($mimes) {
    $mimes['ttf'] = 'font/ttf';
    $mimes['otf'] = 'font/otf';
    $mimes['woff'] = 'font/woff';
    $mimes['woff2'] = 'font/woff2';
    return $mimes;
}
add_filter('upload_mimes', 'allow_font_mime_types');

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
    $btn_color = carbon_get_theme_option('menu_order_btn_color');
    $btn_hover = carbon_get_theme_option('menu_order_btn_hover');
    $btn_text = carbon_get_theme_option('menu_order_btn_text');
    $font_type = carbon_get_theme_option('menu_font_type');
    
    // تنظیم فونت
    $font_style = '';
    if ($font_type === 'custom') {
        $custom_font_name = carbon_get_theme_option('menu_custom_font_name');
        if (!empty($custom_font_name)) {
            $font_style = 'font-family: "' . esc_attr($custom_font_name) . '", Tahoma, sans-serif !important;';
        }
    } elseif ($font_type && $font_type !== 'default') {
        $fonts = array(
            'iransans' => 'IRANSans',
            'shabnam' => 'Shabnam',
            'sahel' => 'Sahel',
        );
        if (isset($fonts[$font_type])) {
            $font_style = 'font-family: "' . $fonts[$font_type] . '", Tahoma, sans-serif !important;';
        }
    }

    echo '<style>
        .simple-menu-wrapper { 
            background-color: ' . esc_attr($bg_color) . '; 
            ' . $font_style . '
        }
        .simple-menu-wrapper .price { color: ' . esc_attr($main_color) . '; }
        .simple-menu-wrapper .cat-breakfast { color: ' . esc_attr($breakfast_color) . '; border-bottom-color: ' . esc_attr($breakfast_color) . '; }
        .simple-menu-wrapper .cat-main-dish { color: ' . esc_attr($main_dish_color) . '; border-bottom-color: ' . esc_attr($main_dish_color) . '; }
        .simple-menu-wrapper .cat-drink { color: ' . esc_attr($drink_color) . '; border-bottom-color: ' . esc_attr($drink_color) . '; }
        .simple-menu-wrapper .item-order-btn { 
            background-color: ' . esc_attr($btn_color) . ' !important;
            color: ' . esc_attr($btn_text) . ' !important;
        }
        .simple-menu-wrapper .item-order-btn:hover { 
            background-color: ' . esc_attr($btn_hover) . ' !important;
        }
        .filter-btn.active { background-color: ' . esc_attr($main_color) . '; border-color: ' . esc_attr($main_color) . '; }
        
        /* ===== وضعیت موجودی ===== */
        .stock-badge-unavailable {
            background-color: #e53935;
            color: white;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
            margin-top: 4px;
        }
        .stock-badge-limited {
            background-color: #ff9800;
            color: white;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
            margin-top: 4px;
        }
        .stock-badge-preorder {
            background-color: #2196F3;
            color: white;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
            margin-top: 4px;
        }
    </style>';

    echo '<div class="simple-menu-wrapper">';
    
    // جعبه جستجو
    echo '<div class="menu-search-box">
        <input type="text" id="menu-search" placeholder="🔍 جستجو در منو...">
    </div>';
    
    $sections = get_terms(array(
        'taxonomy' => 'menu_section',
        'hide_empty' => false,
    ));
    
    // فیلترهای دسته‌بندی
    echo '<div class="cat-filters">';
    echo '<button class="filter-btn active" data-filter="all">همه</button>';
    foreach ($sections as $s) {
        echo '<button class="filter-btn" data-filter="' . esc_attr($s->slug) . '">' . esc_html($s->name) . '</button>';
    }
    echo '</div>';
    
    // فیلترهای پیشرفته
    echo '<div class="advanced-filters">';
    echo '<button class="filter-badge delivery" data-filter="delivery">🚚 ارسال با پیک</button>';
    echo '<button class="filter-badge pickup" data-filter="pickup">🏪 تحویل حضوری</button>';
    echo '<button class="filter-badge discount" data-filter="discount">🔥 تخفیف</button>';
    echo '<button class="filter-badge price-economic" data-filter="economic">💰 اقتصادی</button>';
    echo '<button class="filter-badge price-medium" data-filter="medium">💳 متوسط</button>';
    echo '<button class="filter-badge price-expensive" data-filter="expensive">💎 گران</button>';
    $badge_filters = array(
        'vegetarian' => '🌱 گیاهی',
        'gluten_free' => '🌾 بدون گلوتن',
        'nuts' => '🥜 آجیل',
        'dairy' => '🥛 لبنیات',
        'spicy' => '🌶️ تند'
    );
    foreach ($badge_filters as $key => $label) {
        echo '<button class="filter-badge" data-filter="' . esc_attr($key) . '">' . esc_html($label) . '</button>';
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
                $delivery = carbon_get_post_meta(get_the_ID(), 'menu_item_delivery');
                $pickup = carbon_get_post_meta(get_the_ID(), 'menu_item_pickup');
                $has_coupon = carbon_get_post_meta(get_the_ID(), 'menu_item_has_coupon');
                $coupon_code = carbon_get_post_meta(get_the_ID(), 'menu_item_coupon_code');
                $coupon_title = carbon_get_post_meta(get_the_ID(), 'menu_item_coupon_title');
                $gift = carbon_get_post_meta(get_the_ID(), 'menu_item_gift');
                $price_range = carbon_get_post_meta(get_the_ID(), 'menu_item_price_range');
                
                // ✅ فیلدهای جدید
                $special_badges = carbon_get_post_meta(get_the_ID(), 'menu_item_special_badges');
                $rating = carbon_get_post_meta(get_the_ID(), 'menu_item_rating');
                $prep_time = carbon_get_post_meta(get_the_ID(), 'menu_item_prep_time');
                
                // ✅ وضعیت موجودی
                $stock = carbon_get_post_meta(get_the_ID(), 'menu_item_stock');
                $is_unavailable = ($stock === 'unavailable');
                $is_limited = ($stock === 'limited');
                $is_preorder = ($stock === 'preorder');

                $has_discount = !empty($discount_percent) && is_numeric($discount_percent) && $discount_percent > 0;
                $final_price = $price;
                if ($has_discount) {
                    $final_price = round($price - ($price * $discount_percent / 100));
                }

                // ساخت لیست نشان‌ها برای فیلتر
                $badges_list = is_array($badges) ? implode(',', $badges) : '';
                if ($delivery) $badges_list .= ',delivery';
                if ($pickup) $badges_list .= ',pickup';
                if ($has_discount) $badges_list .= ',discount';
                if (!empty($price_range)) $badges_list .= ',' . $price_range;

                echo '<div class="item" data-category="' . esc_attr($section->slug) . '" data-badges="' . esc_attr($badges_list) . '">';
                
                if (has_post_thumbnail()) {
                    the_post_thumbnail('thumbnail');
                }
                
                echo '<div class="item-details">';
                echo '<div class="item-info">';
                echo '<h3>' . esc_html(get_the_title()) . '</h3>';
                echo '<p>' . esc_html(get_the_excerpt()) . '</p>';

                // ✅ نمایش وضعیت موجودی
                if ($is_unavailable) {
                    echo '<span class="stock-badge-unavailable">❌ ناموجود</span>';
                } elseif ($is_limited) {
                    echo '<span class="stock-badge-limited">⚠️ تعداد محدود</span>';
                } elseif ($is_preorder) {
                    echo '<span class="stock-badge-preorder">📦 پیش‌سفارش</span>';
                }

                // نمایش برچسب‌های ویژه
                if (is_array($special_badges) && !empty($special_badges)) {
                    $badge_colors = array(
                        'best_seller' => '#e74c3c',
                        'chef_special' => '#9b59b6',
                        'new' => '#2ecc71',
                        'limited' => '#f39c12',
                        'healthy' => '#1abc9c',
                        'popular' => '#e67e22',
                    );
                    $badge_labels_special = array(
                        'best_seller' => 'پرفروش‌ترین',
                        'chef_special' => 'ویژه سرآشپز',
                        'new' => 'جدید',
                        'limited' => 'تعداد محدود',
                        'healthy' => 'سالم',
                        'popular' => 'محبوب',
                    );
                    foreach ($special_badges as $badge) {
                        $color = isset($badge_colors[$badge]) ? $badge_colors[$badge] : '#888';
                        $label = isset($badge_labels_special[$badge]) ? $badge_labels_special[$badge] : $badge;
                        echo '<span class="special-badge" style="background:' . $color . ';display:inline-block;font-size:10px;padding:2px 10px;border-radius:12px;margin-top:4px;margin-left:4px;font-weight:bold;color:white;">' . esc_html($label) . '</span>';
                    }
                }

                // نمایش امتیاز
                if ($rating > 0) {
                    echo '<div style="margin-top:5px;font-size:14px;">';
                    for ($i = 1; $i <= $rating; $i++) {
                        echo '⭐';
                    }
                    echo '</div>';
                }

                // نمایش زمان آماده‌سازی
                if (!empty($prep_time)) {
                    echo '<div style="font-size:12px;color:#666;margin-top:4px;">⏱️ ' . esc_html($prep_time) . ' دقیقه</div>';
                }

                // نمایش نشان‌های رژیمی
                if (is_array($badges)) {
                    foreach ($badges as $badge) {
                        $badge_label = isset($badge_labels[$badge]) ? $badge_labels[$badge] : $badge;
                        echo '<span class="badge badge-' . esc_attr($badge) . '">' . esc_html($badge_label) . '</span>';
                    }
                }

                // نمایش نشان‌های تحویل
                if ($delivery) {
                    echo '<span class="badge" style="background:#e3f2fd;color:#1565c0;">🚚 ارسال با پیک</span>';
                }
                if ($pickup) {
                    echo '<span class="badge" style="background:#e8f5e9;color:#2e7d32;">🏪 تحویل حضوری</span>';
                }

                // نمایش کوپن و هدیه
                if ($has_coupon && !empty($coupon_code)) {
                    echo '<div class="coupon-info">🎫 کد: ' . esc_html($coupon_code) . ' - ' . esc_html($coupon_title) . '</div>';
                }
                if (!empty($gift)) {
                    echo '<div class="gift-badge">🎁 ' . esc_html($gift) . '</div>';
                }

                echo '</div>';

                echo '<div class="price-order">';
                echo '<div class="price-order-top">';
                if ($has_discount) {
                    echo '<span class="discount-badge">' . esc_html(convert_to_persian_digits($discount_percent)) . '٪</span>';
                    echo '<p class="original-price">' . esc_html(convert_to_persian_digits($price)) . '</p>';
                }
                echo '<p class="price">' . esc_html(convert_to_persian_digits($final_price)) . ' تومان</p>';
                echo '</div>';// ✅ دکمه سفارش - در صورت ناموجود بودن غیرفعال
                if ($is_unavailable) {
                    echo '<span style="display:inline-block;padding:6px 16px;border-radius:6px;font-size:12px;font-weight:bold;background:#ccc;color:#888;white-space:nowrap;margin-top:8px;">ناموجود</span>';
                } elseif (!empty($item_order_url)) {
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