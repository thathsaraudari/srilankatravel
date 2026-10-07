<?php get_header(); the_post();
$id=get_the_ID();$days=(int)slt_field('duration_days',$id,0);$nights=(int)slt_field('duration_nights',$id,max(0,$days-1));
$tagline=(string)slt_field('short_tagline',$id,get_the_excerpt());$highlights=slt_field('highlights',$id,[]);
$itinerary=slt_field('itinerary',$id,[]);$included=slt_field('included',$id,[]);$excluded=slt_field('excluded',$id,[]);
$destinations=get_the_terms($id,'slt_destination'); ?>
<section class="tour-hero"><div class="tour-hero__image"><?php if(has_post_thumbnail()): the_post_thumbnail('full'); else: ?><img src="<?php echo esc_url('https://images.unsplash.com/photo-1612862862126-865765df2ded?auto=format&fit=crop&w=2200&q=85'); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"><?php endif; ?></div><div class="tour-hero__overlay"></div>
<div class="container tour-hero__content"><div class="eyebrow eyebrow--light">Circuit privé au Sri Lanka</div><h1><?php the_title(); ?></h1><?php if($tagline): ?><p><?php echo esc_html($tagline); ?></p><?php endif; ?>
<div class="tour-facts"><?php if($days): ?><span><strong><?php echo esc_html($days); ?></strong> jours</span><?php endif; ?><?php if($nights): ?><span><strong><?php echo esc_html($nights); ?></strong> nuits</span><?php endif; ?><?php if($destinations&&!is_wp_error($destinations)): ?><span><?php echo esc_html(implode(' · ',wp_list_pluck($destinations,'name'))); ?></span><?php endif; ?></div></div></section>
<div class="container tour-layout"><article class="tour-main">
<section class="tour-section intro-copy"><div class="eyebrow">Aperçu</div><?php the_content(); ?></section>
<?php if($highlights): ?><section class="tour-section"><div class="eyebrow">Temps forts</div><h2>Ce qui rend ce voyage spécial</h2><div class="highlight-grid"><?php foreach($highlights as $item): ?><div class="highlight-pill">✓ <?php echo esc_html($item['text']??''); ?></div><?php endforeach; ?></div></section><?php endif; ?>
<?php if($itinerary): ?><section class="tour-section" id="itinerary"><div class="eyebrow">Jour après jour</div><h2>Votre itinéraire</h2><div class="itinerary">
<?php foreach($itinerary as $row):$hotel=null;$hotel_id=(int)($row['hotel_id']??0);if($hotel_id)$hotel=get_post($hotel_id);if(!$hotel&&isset($row['hotel'])&&$row['hotel'] instanceof WP_Post)$hotel=$row['hotel'];$meals=$row['meals']??[]; ?><article class="itinerary-day"><div class="itinerary-day__number">Jour <?php echo esc_html((string)($row['day']??'')); ?></div><div class="itinerary-day__body"><h3><?php echo esc_html((string)($row['title']??'')); ?></h3>
<?php
$day_image=(int)($row['image_id']??($row['image']??0));
if($day_image):
    echo wp_get_attachment_image($day_image,'large',false,['loading'=>'lazy']);
else:
    $day_no=(int)($row['day']??0);
    $fallbacks=[
        1=>'https://images.unsplash.com/photo-1588598198321-9735fd52455b?auto=format&fit=crop&w=1400&q=82',
        2=>'https://images.unsplash.com/photo-1612862862126-865765df2ded?auto=format&fit=crop&w=1400&q=82',
        3=>'https://images.unsplash.com/photo-1566650576880-6740b03eaad1?auto=format&fit=crop&w=1400&q=82',
        4=>'https://images.unsplash.com/photo-1586193804147-64d5c02ef9c1?auto=format&fit=crop&w=1400&q=82',
        5=>'https://images.unsplash.com/photo-1589373797397-d19670f47549?auto=format&fit=crop&w=1400&q=82',
        6=>'https://images.unsplash.com/photo-1598955890270-d77cdb06d2bb?auto=format&fit=crop&w=1400&q=82',
        7=>'https://images.unsplash.com/photo-1589373797397-d19670f47549?auto=format&fit=crop&w=1400&q=82',
    ];
    $fallback_url=$fallbacks[$day_no]??$fallbacks[2];
?><img class="itinerary-fallback" src="<?php echo esc_url($fallback_url); ?>" alt="<?php echo esc_attr((string)($row['title']??'Sri Lanka')); ?>" loading="lazy"><?php endif; ?><div class="prose"><?php echo wp_kses_post((string)($row['description']??'')); ?></div>
<?php if($hotel||$meals): ?><div class="day-meta"><?php if($hotel instanceof WP_Post): ?><span><strong>Hôtel :</strong> <?php echo esc_html($hotel->post_title); ?></span><?php endif; ?><?php if($meals): ?><span><strong>Repas :</strong> <?php echo esc_html(implode(', ',array_map(fn($m)=>['breakfast'=>'Petit-déjeuner','lunch'=>'Déjeuner','dinner'=>'Dîner'][$m]??ucfirst($m),$meals))); ?></span><?php endif; ?></div><?php endif; ?>
</div></article><?php endforeach; ?></div></section><?php endif; ?>
<?php if($included||$excluded): ?><section class="tour-section inclusions-grid"><?php if($included): ?><div><div class="eyebrow">Inclus</div><h2>Ce qui est inclus</h2><ul class="check-list"><?php foreach($included as $row): ?><li>✓ <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?><?php if($excluded): ?><div><div class="eyebrow">À savoir</div><h2>Non inclus</h2><ul class="cross-list"><?php foreach($excluded as $row): ?><li>– <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?></section><?php endif; ?>
</article><aside class="tour-sidebar"><div class="quote-card booking-card">
<div class="eyebrow">Réserver ce circuit</div>
<div class="quote-card__price"><?php echo esc_html(slt_price_label($id)); ?></div>
<?php if(isset($_GET['enquiry'])&&$_GET['enquiry']==='success'): ?><div class="slt-notice">Votre réservation de démonstration a bien été enregistrée.</div><?php endif; ?>
<div class="slt-demo-badge">Mode démonstration — aucun paiement réel ne sera débité.</div>
<form class="slt-booking-demo-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
<input type="hidden" name="action" value="slt_submit_enquiry">
<input type="hidden" name="tour_id" value="<?php echo esc_attr((string)$id); ?>">
<input type="hidden" name="message" class="slt-booking-message" value="Réservation démo — moyen de paiement souhaité : Wero">
<?php wp_nonce_field('slt_submit_enquiry','slt_nonce'); ?>
<div class="slt-form-grid">
<label>Nom<input name="name" required autocomplete="name"></label>
<label>Email<input type="email" name="email" required autocomplete="email"></label>
<label>Date de départ<input type="date" name="travel_date" required></label>
<label>Adultes<input type="number" name="adults" min="1" value="2" required></label>
<label>Enfants<input type="number" name="children" min="0" value="0"></label>
<label>Téléphone<input name="phone" autocomplete="tel"></label>
</div>
<fieldset class="slt-payment-methods"><legend>Moyen de paiement souhaité</legend>
<label class="slt-pay-option slt-pay-option--featured"><input type="radio" name="preferred_payment_method" value="Wero" checked><span><strong>Wero</strong><small>Paiement bancaire européen, si disponible</small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="Carte bancaire"><span><strong>Carte bancaire</strong><small>CB / Visa / Mastercard</small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="Apple Pay"><span><strong>Apple Pay</strong><small>Si disponible sur votre appareil</small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="iDEAL"><span><strong>iDEAL</strong><small>Pour les clients néerlandais</small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="PayPal"><span><strong>PayPal</strong></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="SEPA"><span><strong>Virement SEPA</strong></span></label>
</fieldset>
<label class="slt-checkbox"><input type="checkbox" name="privacy" value="1" required><span>J’accepte que mes informations soient utilisées pour traiter cette demande de réservation.</span></label>
<button class="slt-button" type="submit">Créer ma réservation (démo)</button>
<p class="slt-secure-note">Le paiement Mollie sera connecté dans une étape séparée après validation de ce parcours.</p>
</form>
</div></aside></div>
<?php get_footer(); ?>
