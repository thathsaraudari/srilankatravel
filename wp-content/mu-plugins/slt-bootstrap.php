<?php
/**
 * Plugin Name: SLT Bootstrap
 * Description: One-time setup for the Sri Lanka Travel demo site.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;

add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v1')) {
        return;
    }

    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $plugin = 'slt-core/slt-core.php';
    if (!is_plugin_active($plugin)) {
        $result = activate_plugin($plugin);
        if (is_wp_error($result)) {
            update_option('slt_bootstrap_error', $result->get_error_message(), false);
        }
        return;
    }

    $theme = wp_get_theme('slt-travel');
    if ($theme->exists() && get_stylesheet() !== 'slt-travel') {
        switch_theme('slt-travel');
        return;
    }

    $contact = get_page_by_path('contact');
    if (!$contact) {
        $contact_id = wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Contact',
            'post_name' => 'contact',
            'post_content' => '<p>Tell us your preferred dates, number of travellers and what you would like to experience in Sri Lanka.</p>[slt_enquiry_form tour_id="0"]',
        ]);
    } else {
        $contact_id = $contact->ID;
    }

    $locations = get_theme_mod('nav_menu_locations', []);
    $menu_name = 'Primary';
    $menu = wp_get_nav_menu_object($menu_name);
    $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu($menu_name);

    if (!is_wp_error($menu_id)) {
        $items = wp_get_nav_menu_items($menu_id) ?: [];
        if (!$items) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Home',
                'menu-item-url' => home_url('/'),
                'menu-item-status' => 'publish',
                'menu-item-type' => 'custom',
            ]);
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => 'Tours',
                'menu-item-url' => get_post_type_archive_link('slt_tour') ?: home_url('/tours/'),
                'menu-item-status' => 'publish',
                'menu-item-type' => 'custom',
            ]);
            if (!empty($contact_id) && !is_wp_error($contact_id)) {
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title' => 'Contact',
                    'menu-item-object-id' => (int) $contact_id,
                    'menu-item-object' => 'page',
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'post_type',
                ]);
            }
        }
        $locations['primary'] = $menu_id;
        $locations['footer'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    update_option('slt_bootstrap_completed_v1', 1, false);
    delete_option('slt_bootstrap_error');
});
