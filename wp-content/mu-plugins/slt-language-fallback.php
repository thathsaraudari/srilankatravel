<?php
/**
 * Plugin Name: SLT Language Fallback
 * Description: Self-contained top-right language switcher independent of theme templates.
 * Version: 1.2.0
 */
if (!defined('ABSPATH')) exit;

function slt_fallback_language_context(): array {
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

    $urls=[];
    foreach($langs as $code=>$label){
        $urls[$code]=function_exists('slt_language_switch_url')
            ? slt_language_switch_url($code)
            : add_query_arg('lang',$code);
    }

    return [$langs,$current,$urls];
}

add_action('wp_head', function (): void {
    if (is_admin()) return;
    [$langs,$current,$urls]=slt_fallback_language_context();
    ?>
    <script id="slt-language-launcher">
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('slt-language-topright')) return;

        const langs = <?php echo wp_json_encode($langs); ?>;
        const urls = <?php echo wp_json_encode($urls); ?>;
        const current = <?php echo wp_json_encode($current); ?>;

        const wrap = document.createElement('div');
        wrap.id = 'slt-language-topright';
        wrap.setAttribute('style',
            'position:fixed;right:14px;top:14px;z-index:2147483647;' +
            'display:flex;align-items:center;gap:4px;padding:5px 7px;' +
            'background:#fff;border:1px solid #cfcac0;border-radius:999px;' +
            'box-shadow:0 6px 22px rgba(0,0,0,.16);font-family:Arial,sans-serif;'
        );

        const globe = document.createElement('span');
        globe.textContent = '🌐';
        globe.setAttribute('style','font-size:14px;line-height:1;padding:0 3px;');
        wrap.appendChild(globe);

        Object.keys(langs).forEach(function (code) {
            const a = document.createElement('a');
            a.href = urls[code];
            a.textContent = langs[code];
            a.setAttribute('style',
                'display:inline-block;padding:6px 7px;border-radius:999px;' +
                'text-decoration:none;font-size:11px;line-height:1;font-weight:700;' +
                (code === current
                    ? 'background:#1f4c3b;color:#fff;'
                    : 'background:transparent;color:#18221f;')
            );
            wrap.appendChild(a);
        });

        document.body.appendChild(wrap);
    });
    </script>
    <?php
}, 9999);
