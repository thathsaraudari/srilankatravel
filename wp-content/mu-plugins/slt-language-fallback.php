<?php
/**
 * Plugin Name: SLT Language Fallback
 * Description: Always-visible language switcher fallback independent of theme header layout.
 * Version: 1.1.0
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

    echo '<div id="slt-language-fallback" style="position:fixed;right:14px;top:14px;bottom:auto;z-index:2147483647;background:#fff;border:1px solid #cfcac0;border-radius:999px;box-shadow:0 6px 22px rgba(0,0,0,.16);padding:5px 7px;font-family:Arial,sans-serif;color:#18221f;display:flex;align-items:center;gap:4px">';
    echo '<span aria-hidden="true" style="font-size:14px;line-height:1;padding:0 3px">🌐</span>';
    foreach($langs as $code=>$label){
        $url=function_exists('slt_language_switch_url') ? slt_language_switch_url($code) : add_query_arg('lang',$code);
        $active=$current===$code;
        $style='display:inline-block;padding:6px 7px;border-radius:999px;text-decoration:none;font-size:11px;line-height:1;font-weight:700;';
        $style.=$active?'background:#1f4c3b;color:#fff;':'background:transparent;color:#18221f;';
        echo '<a href="'.esc_url($url).'" style="'.esc_attr($style).'">'.esc_html($label).'</a>';
    }
    echo '</div>';
}, 9999);
