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
<?php $current_lang=slt_current_language(); $langs=slt_supported_languages(); ?>
<details class="language-menu">
  <summary aria-label="<?php echo esc_attr(slt_t('language','Langue')); ?>"><span aria-hidden="true">🌐</span> <?php echo esc_html($langs[$current_lang]['short']??strtoupper($current_lang)); ?></summary>
  <div class="language-menu__panel">
    <?php foreach($langs as $code=>$meta): ?>
      <a href="<?php echo esc_url(slt_language_switch_url($code)); ?>" lang="<?php echo esc_attr($code); ?>"<?php echo $current_lang===$code?' aria-current="page"':''; ?>>
        <strong><?php echo esc_html($meta['short']); ?></strong><span><?php echo esc_html($meta['label']); ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</details>
<a class="button button--small header-cta" href="<?php echo esc_url(home_url('/contact/')); ?>"><?php echo esc_html(slt_t('nav.cta','Créer mon voyage')); ?></a>
</div></header><main>
