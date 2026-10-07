<?php
if (!defined('ABSPATH')) exit;

function slt_theme_setup(): void {
    add_theme_support('title-tag');add_theme_support('post-thumbnails');
    add_theme_support('html5',['search-form','gallery','caption','style','script']);add_theme_support('responsive-embeds');add_theme_support('woocommerce');
    register_nav_menus(['primary'=>__('Primary navigation','slt-travel'),'footer'=>__('Footer navigation','slt-travel')]);
}
add_action('after_setup_theme','slt_theme_setup');

function slt_enqueue_assets(): void {
    wp_enqueue_style('slt-main',get_template_directory_uri().'/assets/css/main.css',[],'0.3.0');
    wp_enqueue_script('slt-main',get_template_directory_uri().'/assets/js/main.js',[],'0.3.0',true);
}
add_action('wp_enqueue_scripts','slt_enqueue_assets');

function slt_field(string $name,$post_id=false,$default=null){
    $id=$post_id?:get_the_ID();$value=get_post_meta((int)$id,$name,true);
    return ($value!==null&&$value!==false&&$value!=='')?$value:$default;
}
function slt_site_option(string $name,$default=null){
    $settings=get_option('slt_settings',[]);
    return array_key_exists($name,$settings)&&$settings[$name]!==''?$settings[$name]:$default;
}
function slt_price_label(int $post_id): string {
    $basis=slt_field('price_basis',$post_id,'person');$price=slt_field('price_from',$post_id,null);
    if($basis==='request'||!$price)return __('Prix à confirmer','slt-travel');
    $suffix=['person'=>' / personne','couple'=>' / couple','trip'=>' / voyage'][$basis]??'';
    return sprintf('À partir de €%s%s',number_format_i18n((float)$price,0),$suffix);
}
function slt_render_tour_card(int $post_id): void {
    $days=(int)slt_field('duration_days',$post_id,0);$tagline=(string)slt_field('short_tagline',$post_id,get_the_excerpt($post_id));
    $fallback=get_template_directory_uri().'/assets/images/hero.webp'; ?>
    <article class="tour-card">
        <a class="tour-card__image" href="<?php echo esc_url(get_permalink($post_id)); ?>">
            <?php if(has_post_thumbnail($post_id)):echo get_the_post_thumbnail($post_id,'large',['loading'=>'lazy']);else:?><img src="<?php echo esc_url($fallback); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy"><?php endif; ?>
        </a>
        <div class="tour-card__body">
            <?php if($days): ?><div class="eyebrow"><?php echo esc_html($days.' jours'); ?></div><?php endif; ?>
            <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
            <?php if($tagline): ?><p><?php echo esc_html($tagline); ?></p><?php endif; ?>
            <div class="tour-card__footer"><strong><?php echo esc_html(slt_price_label($post_id)); ?></strong><a class="text-link" href="<?php echo esc_url(get_permalink($post_id)); ?>">Voir & réserver →</a></div>
        </div>
    </article>
<?php }
