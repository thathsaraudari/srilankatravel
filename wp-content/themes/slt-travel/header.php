<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<header class="site-header"><div class="container nav-wrap">
<a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
<img class="brand__logo" src="<?php echo esc_url(get_template_directory_uri().'/assets/images/logo-mark.svg'); ?>" width="44" height="44" alt="">
<span class="brand__copy"><strong><?php bloginfo('name'); ?></strong><small>Voyages privés au Sri Lanka</small></span>
</a>
<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">Menu</button>
<nav id="primary-nav" class="primary-nav"><?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'fallback_cb'=>false]); ?></nav>
<a class="button button--small header-cta" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Réserver</a>
</div></header><main>
