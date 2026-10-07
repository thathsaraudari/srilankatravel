<?php get_header(); the_post();
$id=get_the_ID();$days=(int)slt_field('duration_days',$id,0);$nights=(int)slt_field('duration_nights',$id,max(0,$days-1));
$tagline=(string)slt_field('short_tagline',$id,get_the_excerpt());$highlights=slt_field('highlights',$id,[]);
$itinerary=slt_field('itinerary',$id,[]);$included=slt_field('included',$id,[]);$excluded=slt_field('excluded',$id,[]);
$destinations=get_the_terms($id,'slt_destination');
$fallback_hero=get_template_directory_uri().'/assets/images/hero.webp';
$fallback_day=get_template_directory_uri().'/assets/images/dambulla.webp'; ?>
<section class="tour-hero"><div class="tour-hero__image"><?php if(has_post_thumbnail()): the_post_thumbnail('full'); else: ?><img src="<?php echo esc_url($fallback_hero); ?>" alt="Paysage du Sri Lanka"><?php endif; ?></div><div class="tour-hero__overlay"></div>
<div class="container tour-hero__content"><div class="eyebrow eyebrow--light">Circuit privé au Sri Lanka</div><h1><?php the_title(); ?></h1><?php if($tagline): ?><p><?php echo esc_html($tagline); ?></p><?php endif; ?>
<div class="tour-facts"><?php if($days): ?><span><strong><?php echo esc_html((string)$days); ?></strong> jours</span><?php endif; ?><?php if($nights): ?><span><strong><?php echo esc_html((string)$nights); ?></strong> nuits</span><?php endif; ?><?php if($destinations&&!is_wp_error($destinations)): ?><span><?php echo esc_html(implode(' · ',wp_list_pluck($destinations,'name'))); ?></span><?php endif; ?></div></div></section>

<div class="container tour-layout"><article class="tour-main">
<section class="tour-section intro-copy"><div class="eyebrow">Aperçu</div><?php the_content(); ?></section>
<?php if($highlights): ?><section class="tour-section"><div class="eyebrow">Temps forts</div><h2>Ce qui rend ce voyage spécial</h2><div class="highlight-grid"><?php foreach($highlights as $item): ?><div class="highlight-pill">✓ <?php echo esc_html($item['text']??''); ?></div><?php endforeach; ?></div></section><?php endif; ?>

<?php if($itinerary): ?><section class="tour-section" id="itinerary"><div class="eyebrow">Jour après jour</div><h2>Votre itinéraire</h2><div class="itinerary">
<?php foreach($itinerary as $row):$hotel=null;$hotel_id=(int)($row['hotel_id']??0);if($hotel_id)$hotel=get_post($hotel_id);$meals=$row['meals']??[];$day=(int)($row['day']??0); ?><article class="itinerary-day"><div class="itinerary-day__number">Jour <?php echo esc_html((string)$day); ?></div><div class="itinerary-day__body"><h3><?php echo esc_html((string)($row['title']??'')); ?></h3>
<?php $day_image=(int)($row['image_id']??0);if($day_image):echo wp_get_attachment_image($day_image,'large',false,['loading'=>'lazy']);else:?><img class="itinerary-fallback" src="<?php echo esc_url($day%2===0?$fallback_hero:$fallback_day); ?>" alt="<?php echo esc_attr((string)($row['title']??'Sri Lanka')); ?>" loading="lazy"><?php endif; ?>
<div class="prose"><?php echo wp_kses_post((string)($row['description']??'')); ?></div>
<?php if($hotel||$meals): ?><div class="day-meta"><?php if($hotel instanceof WP_Post): ?><span><strong>Hôtel :</strong> <?php echo esc_html($hotel->post_title); ?></span><?php endif; ?><?php if($meals): ?><span><strong>Repas :</strong> <?php echo esc_html(implode(', ',array_map(fn($m)=>['breakfast'=>'Petit-déjeuner','lunch'=>'Déjeuner','dinner'=>'Dîner'][$m]??ucfirst($m),$meals))); ?></span><?php endif; ?></div><?php endif; ?>
</div></article><?php endforeach; ?></div></section><?php endif; ?>

<?php if($included||$excluded): ?><section class="tour-section inclusions-grid"><?php if($included): ?><div><div class="eyebrow">Inclus</div><h2>Ce qui est inclus</h2><ul class="check-list"><?php foreach($included as $row): ?><li>✓ <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?><?php if($excluded): ?><div><div class="eyebrow">À savoir</div><h2>Non inclus</h2><ul class="cross-list"><?php foreach($excluded as $row): ?><li>– <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?></section><?php endif; ?>

<section class="tour-section payment-explainer"><div class="eyebrow">Paiement</div><h2>Réservez avec un moyen de paiement européen</h2><p>Le checkout est préparé pour Mollie. En mode démonstration, vous pouvez tester tout le parcours sans débit réel. Une fois Mollie connecté, l’acompte sera réglé sur la page de paiement sécurisée Mollie.</p><div class="payment-chips"><span>Wero</span><span>Carte bancaire</span><span>Apple Pay</span><span>iDEAL</span><span>PayPal</span><span>SEPA</span></div></section>
</article>

<aside class="tour-sidebar"><div class="quote-card booking-card"><div class="eyebrow">Réserver ce circuit</div><div class="quote-card__price"><?php echo esc_html(slt_price_label($id)); ?></div><p>Choisissez votre date, vos voyageurs et votre moyen de paiement. Les disponibilités restent soumises à confirmation.</p><?php echo do_shortcode('[slt_booking_form tour_id="'.$id.'"]'); ?></div></aside></div>
<?php get_footer(); ?>
