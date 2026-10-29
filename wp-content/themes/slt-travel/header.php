<!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>><?php wp_body_open(); ?>
<header class="site-header"><div class="container nav-wrap">
<a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
<img class="brand__logo brand__logo--wide" src="<?php echo esc_url(get_template_directory_uri().'/assets/images/logo-mark.svg?v=3'); ?>" width="180" height="140" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
</a>
<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav"><?php echo esc_html(slt_t('menu','Menu')); ?></button>
<nav id="primary-nav" class="primary-nav"><?php wp_nav_menu(['theme_location'=>'primary','container'=>false,'fallback_cb'=>false]); ?></nav>
<?php $current_lang=slt_current_language(); $langs=slt_supported_languages(); ?>
<label class="slt-language-picker" aria-label="Choose site language">
  <span class="slt-language-picker__icon" aria-hidden="true">🌐</span>
  <select class="slt-language-picker__select" data-slt-language aria-label="Choose site language">
    <?php foreach($langs as $code=>$meta): ?>
      <option value="<?php echo esc_url(slt_language_switch_url($code)); ?>" <?php selected($current_lang,$code); ?>><?php echo esc_html($meta['short']); ?></option>
    <?php endforeach; ?>
  </select>
</label>
<a class="button button--small header-cta" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html(slt_t('nav.cta','Créer mon voyage')); ?></a>
</div></header><main>
