<?php
if (!defined('ABSPATH')) exit;

require_once get_template_directory().'/inc/i18n.php';

function slt_theme_setup(): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form','gallery','caption','style','script']);
    add_theme_support('responsive-embeds');
    add_theme_support('woocommerce');
    register_nav_menus(['primary'=>__('Primary navigation','slt-travel'),'footer'=>__('Footer navigation','slt-travel')]);
}
add_action('after_setup_theme','slt_theme_setup');

function slt_enqueue_assets(): void {
    wp_enqueue_style('slt-main',get_template_directory_uri().'/assets/css/main.css',[],'0.4.3');
    wp_enqueue_script('slt-main',get_template_directory_uri().'/assets/js/main.js',[],'0.4.3',true);
    wp_localize_script('slt-main','SLT_I18N',[
        'lang'=>slt_current_language(),
        'strings'=>[
            'confirm'=>slt_t('js.confirm','Prix à confirmer'),
            'final'=>slt_t('js.final','Le prix final sera confirmé avant réservation.'),
            'no_rate'=>slt_t('js.no_rate','Aucun tarif n’est configuré pour cette taille de groupe.'),
            'adult'=>slt_t('js.adult','adulte'),
            'child'=>slt_t('js.child','enfant'),
            'single'=>slt_t('js.single','chambre individuelle'),
            'season'=>slt_t('js.season','Supplément haute saison de %s%% inclus.'),
        ],
    ]);
}
add_action('wp_enqueue_scripts','slt_enqueue_assets');

function slt_field(string $name,$post_id=false,$default=null) {
    $id=$post_id ?: get_the_ID();
    if (function_exists('get_field')) {
        $value=get_field($name,$id);
        if ($value!==null && $value!==false && $value!=='') return $value;
    }
    $value=get_post_meta((int)$id,$name,true);
    $value=($value!==null && $value!==false && $value!=='') ? $value : $default;
    if(get_post_type((int)$id)==='slt_tour')$value=slt_translate_tour_field($name,(int)$id,$value);
    return $value;
}
function slt_site_option(string $name,$default=null) {
    if (function_exists('get_field')) {
        $value=get_field($name,'option');
        if ($value!==null && $value!==false && $value!=='') return $value;
    }
    $settings=get_option('slt_settings',[]);
    return array_key_exists($name,$settings) && $settings[$name]!=='' ? $settings[$name] : $default;
}
function slt_price_label(int $post_id): string {
    $basis=slt_field('price_basis',$post_id,'person');
    $price=slt_field('price_from',$post_id,null);
    if ($basis==='request' || !$price) return slt_t('price.request','Prix sur demande');
    $suffix=['person'=>slt_t('price.person',' / personne'),'couple'=>slt_t('price.couple',' / couple'),'trip'=>slt_t('price.trip',' / voyage')][$basis]??'';
    $currency=slt_site_option('currency','EUR');
    $symbol=['EUR'=>'€','USD'=>'$','GBP'=>'£'][$currency]??$currency.' ';
    return sprintf('%s %s%s%s',slt_t('price.from','À partir de'),$symbol,number_format_i18n((float)$price,0),$suffix);
}
function slt_render_tour_card(int $post_id): void {
    $days=(int)slt_field('duration_days',$post_id,0);
    $tagline=(string)slt_field('short_tagline',$post_id,get_the_excerpt($post_id));
    $fallback=(string)slt_field('demo_image_url',$post_id,'https://images.unsplash.com/photo-1612862862126-865765df2ded?auto=format&fit=crop&w=1400&q=82'); ?>
    <article class="tour-card">
        <a class="tour-card__image" href="<?php echo esc_url(get_permalink($post_id)); ?>">
            <?php if(has_post_thumbnail($post_id)): echo get_the_post_thumbnail($post_id,'large',['loading'=>'lazy']); else: ?><img src="<?php echo esc_url($fallback); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" loading="lazy"><?php endif; ?>
        </a>
        <div class="tour-card__body">
            <?php if($days): ?><div class="eyebrow"><?php echo esc_html($days.' '.slt_t('tour.days','jours')); ?></div><?php endif; ?>
            <h3><a href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(get_the_title($post_id)); ?></a></h3>
            <?php if($tagline): ?><p><?php echo esc_html($tagline); ?></p><?php endif; ?>
            <div class="tour-card__footer"><strong><?php echo esc_html(slt_price_label($post_id)); ?></strong><a class="text-link" href="<?php echo esc_url(get_permalink($post_id)); ?>"><?php echo esc_html(slt_t('card.view','Voir le circuit →')); ?></a></div>
        </div>
    </article>
<?php }

