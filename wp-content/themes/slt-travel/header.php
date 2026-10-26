<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<header class="site-header"><div class="container nav-wrap">
<a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
<img class="brand__logo brand__logo--wide" src="<?php echo esc_url(get_template_directory_uri().'/assets/images/logo-mark.svg'); ?>" width="188" height="54" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
</a>
<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav"><?php echo esc_html(slt_t('menu','Menu')); ?></button>
<nav id="primary-nav" class="primary-nav"><?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'fallback_cb'=>false]); ?></nav>
<div class="language-switcher language-switcher--fixed">
<span class="language-switcher__icon" aria-hidden="true">🌐</span>
<select data-slt-language aria-label="<?php echo esc_attr(slt_t('language','Langue')); ?>"><?php echo slt_language_options_html(); ?></select>
</div>
<a class="button button--small header-cta" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html(slt_t('nav.cta','Créer mon voyage')); ?></a>
</div></header><main>
