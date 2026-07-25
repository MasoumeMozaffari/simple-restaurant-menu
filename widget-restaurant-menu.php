<?php
class Restaurant_Menu_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'restaurant_menu_widget';
    }

    public function get_title() {
        return 'منوی رستوران';
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return array('general');
    }

    protected function render() {
        echo display_restaurant_menu();
    }
}