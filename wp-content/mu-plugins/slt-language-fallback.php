<?php
/**
 * Plugin Name: SLT Language Fallback
 * Description: Always-visible language switcher fallback independent of theme header layout.
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function (): void {
    if (is_admin()) return;

    $langs=[
        'fr'=>'FR',
        'en'=>'EN',
        'de'=>'DE',
        'nl'=>'NL',
        'es'=>'ES',
    ];

    $current='fr';
    if (function_exists('slt_current_language')) {
        $current=slt_current_language();
    } elseif (!empty($_GET['lang']) && isset($langs[sanitize_key(wp_unslash($_GET['lang']))])) {
        $current=sanitize_key(wp_unslash($_GET['lang']));
    } elseif (!empty($_COOKIE['slt_lang']) && isset($langs[sanitize_key(wp_unslash($_COOKIE['slt_lang']))])) {
        $current=sanitize_key(wp_unslash($_COOKIE['slt_lang']));
    }

    echo '<div id="slt-language-fallback" style="position:fixed;right:18px;bottom:18px;z-index:2147483647;background:#fff;border:2px solid #1f4c3b;border-radius:14px;box-shadow:0 12px 35px rgba(0,0,0,.25);padding:10px 12px;font-family:Arial,sans-serif;color:#18221f">';
    echo '<div style="font-size:13px;font-weight:700;margin-bottom:7px">🌐 Language</div>';
    echo '<div style="display:flex;gap:6px">';
    foreach($langs as $code=>$label){
        $url=function_exists('slt_language_switch_url') ? slt_language_switch_url($code) : add_query_arg('lang',$code);
        $active=$current===$code;
        $style='display:inline-block;padding:6px 8px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;border:1px solid #d5d5d5;';
        $style.=$active?'background:#1f4c3b;color:#fff;':'background:#fff;color:#18221f;';
        echo '<a href="'.esc_url($url).'" style="'.esc_attr($style).'">'.esc_html($label).'</a>';
    }
    echo '</div></div>';
}, 9999);
