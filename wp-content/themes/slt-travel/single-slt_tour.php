<?php get_header(); the_post();
$id=get_the_ID();$days=(int)slt_field('duration_days',$id,0);$nights=(int)slt_field('duration_nights',$id,max(0,$days-1));
$tagline=(string)slt_field('short_tagline',$id,get_the_excerpt());$highlights=slt_field('highlights',$id,[]);
$itinerary=slt_field('itinerary',$id,[]);$included=slt_field('included',$id,[]);$excluded=slt_field('excluded',$id,[]);
$destinations=get_the_terms($id,'slt_destination');
$pricing=class_exists('SLT_Core')?SLT_Core::pricing_data($id):[];
$fallback_hero=(string)slt_field('demo_image_url',$id,'https://images.unsplash.com/photo-1612862862126-865765df2ded?auto=format&fit=crop&w=2200&q=85'); ?>
<section class="tour-hero"><div class="tour-hero__image"><?php if(has_post_thumbnail()): the_post_thumbnail('full'); else: ?><img src="<?php echo esc_url($fallback_hero); ?>" alt="<?php echo esc_attr(get_the_title()); ?>"><?php endif; ?></div><div class="tour-hero__overlay"></div>
<div class="container tour-hero__content"><div class="eyebrow eyebrow--light"><?php echo esc_html(slt_t('tour.private','Circuit privé au Sri Lanka')); ?></div><h1><?php the_title(); ?></h1><?php if($tagline): ?><p><?php echo esc_html($tagline); ?></p><?php endif; ?>
<div class="tour-facts"><?php if($days): ?><span><strong><?php echo esc_html($days); ?></strong> <?php echo esc_html(slt_t('tour.days','jours')); ?></span><?php endif; ?><?php if($nights): ?><span><strong><?php echo esc_html($nights); ?></strong> <?php echo esc_html(slt_t('tour.nights','nuits')); ?></span><?php endif; ?><?php if($destinations&&!is_wp_error($destinations)): ?><span><?php echo esc_html(implode(' · ',wp_list_pluck($destinations,'name'))); ?></span><?php endif; ?></div></div></section>
<div class="container tour-layout"><article class="tour-main">
<section class="tour-section intro-copy"><div class="eyebrow"><?php echo esc_html(slt_t('tour.overview','Aperçu')); ?></div><?php the_content(); ?></section>
<?php if($highlights): ?><section class="tour-section"><div class="eyebrow"><?php echo esc_html(slt_t('tour.highlights','Temps forts')); ?></div><h2><?php echo esc_html(slt_t('tour.special','Ce qui rend ce voyage spécial')); ?></h2><div class="highlight-grid"><?php foreach($highlights as $item): ?><div class="highlight-pill">✓ <?php echo esc_html($item['text']??''); ?></div><?php endforeach; ?></div></section><?php endif; ?>
<?php if($itinerary): ?><section class="tour-section" id="itinerary"><div class="eyebrow"><?php echo esc_html(slt_t('tour.daybyday','Jour après jour')); ?></div><h2><?php echo esc_html(slt_t('tour.itinerary','Votre itinéraire')); ?></h2><div class="itinerary">
<?php foreach($itinerary as $row):$hotel=null;$hotel_id=(int)($row['hotel_id']??0);if($hotel_id)$hotel=get_post($hotel_id);if(!$hotel&&isset($row['hotel'])&&$row['hotel'] instanceof WP_Post)$hotel=$row['hotel'];$meals=$row['meals']??[]; ?><article class="itinerary-day"><div class="itinerary-day__number"><?php echo esc_html(slt_t('tour.day','Jour')); ?> <?php echo esc_html((string)($row['day']??'')); ?></div><div class="itinerary-day__body"><h3><?php echo esc_html((string)($row['title']??'')); ?></h3>
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
<?php if($hotel||$meals): ?><div class="day-meta"><?php if($hotel instanceof WP_Post): ?><span><strong><?php echo esc_html(slt_t('tour.hotel','Hôtel')); ?> :</strong> <?php echo esc_html($hotel->post_title); ?></span><?php endif; ?><?php if($meals): ?><span><strong><?php echo esc_html(slt_t('tour.meals','Repas')); ?> :</strong> <?php echo esc_html(implode(', ',array_map(fn($m)=>['breakfast'=>slt_t('tour.breakfast','Petit-déjeuner'),'lunch'=>slt_t('tour.lunch','Déjeuner'),'dinner'=>slt_t('tour.dinner','Dîner')][$m]??ucfirst($m),$meals))); ?></span><?php endif; ?></div><?php endif; ?>
</div></article><?php endforeach; ?></div></section><?php endif; ?>
<?php if($included||$excluded): ?><section class="tour-section inclusions-grid"><?php if($included): ?><div><div class="eyebrow"><?php echo esc_html(slt_t('tour.included','Inclus')); ?></div><h2><?php echo esc_html(slt_t('tour.whats_included','Ce qui est inclus')); ?></h2><ul class="check-list"><?php foreach($included as $row): ?><li>✓ <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?><?php if($excluded): ?><div><div class="eyebrow"><?php echo esc_html(slt_t('tour.know','À savoir')); ?></div><h2><?php echo esc_html(slt_t('tour.not_included','Non inclus')); ?></h2><ul class="cross-list"><?php foreach($excluded as $row): ?><li>– <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?></section><?php endif; ?>
</article><aside class="tour-sidebar"><div class="quote-card booking-card">
<div class="eyebrow"><?php echo esc_html(slt_t('booking.title','Réserver ce circuit')); ?></div>
<div class="quote-card__price"><?php echo esc_html(slt_price_label($id)); ?></div>
<?php if(isset($_GET['enquiry'])&&$_GET['enquiry']==='success'): ?><div class="slt-notice"><?php echo esc_html(slt_t('booking.success','Votre réservation de démonstration a bien été enregistrée.')); ?></div><?php endif; ?>
<div class="slt-demo-badge"><?php echo esc_html(slt_t('booking.demo','Mode démonstration — aucun paiement réel ne sera débité.')); ?></div>
<form class="slt-booking-demo-form slt-priced-booking" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"
data-pricing-mode="<?php echo esc_attr((string)($pricing['mode']??'request')); ?>"
data-price-1="<?php echo esc_attr((string)($pricing['price_1']??0)); ?>"
data-price-2="<?php echo esc_attr((string)($pricing['price_2']??0)); ?>"
data-price-3-4="<?php echo esc_attr((string)($pricing['price_3_4']??0)); ?>"
data-price-5-6="<?php echo esc_attr((string)($pricing['price_5_6']??0)); ?>"
data-price-7-plus="<?php echo esc_attr((string)($pricing['price_7_plus']??0)); ?>"
data-child-discount="<?php echo esc_attr((string)($pricing['child_discount_percent']??0)); ?>"
data-single-supplement="<?php echo esc_attr((string)($pricing['single_room_supplement']??0)); ?>"
data-season-start="<?php echo esc_attr((string)($pricing['season_start']??'')); ?>"
data-season-end="<?php echo esc_attr((string)($pricing['season_end']??'')); ?>"
data-season-surcharge="<?php echo esc_attr((string)($pricing['season_surcharge_percent']??0)); ?>"
data-deposit-percent="<?php echo esc_attr((string)($pricing['deposit_percent']??30)); ?>"
data-currency="<?php echo esc_attr((string)($pricing['currency']??'EUR')); ?>">
<input type="hidden" name="action" value="slt_submit_enquiry">
<input type="hidden" name="tour_id" value="<?php echo esc_attr((string)$id); ?>">
<input type="hidden" name="message" class="slt-booking-message" value="Booking demo">
<?php wp_nonce_field('slt_submit_enquiry','slt_nonce'); ?>
<div class="slt-form-grid">
<label><?php echo esc_html(slt_t('booking.name','Nom')); ?><input name="name" required autocomplete="name"></label>
<label><?php echo esc_html(slt_t('booking.email','Email')); ?><input type="email" name="email" required autocomplete="email"></label>
<label><?php echo esc_html(slt_t('booking.date','Date de départ')); ?><input type="date" name="travel_date" required></label>
<label><?php echo esc_html(slt_t('booking.adults','Adultes')); ?><input type="number" name="adults" min="1" value="2" required></label>
<label><?php echo esc_html(slt_t('booking.children','Enfants')); ?><input type="number" name="children" min="0" value="0"></label>
<label><?php echo esc_html(slt_t('booking.single','Chambres individuelles')); ?><input type="number" name="single_rooms" min="0" value="0"><small><?php echo esc_html(slt_t('booking.single_help','Supplément appliqué par chambre individuelle.')); ?></small></label>
<label><?php echo esc_html(slt_t('booking.phone','Téléphone')); ?><input name="phone" autocomplete="tel"></label>
</div>
<div class="slt-live-price" aria-live="polite">
  <div class="slt-live-price__row"><span><?php echo esc_html(slt_t('booking.total','Prix total')); ?></span><strong data-slt-total>—</strong></div>
  <div class="slt-live-price__detail"><span data-slt-rate-note><?php echo esc_html(slt_t('booking.calc','Renseignez vos voyageurs pour calculer le prix.')); ?></span></div>
  <div class="slt-live-price__row slt-live-price__deposit"><span><?php echo esc_html(slt_t('booking.deposit','Acompte à la réservation')); ?></span><strong data-slt-deposit>—</strong></div>
  <div class="slt-live-price__season" data-slt-season hidden><?php echo esc_html(slt_t('booking.season','Supplément haute saison inclus.')); ?></div>
</div>
<fieldset class="slt-payment-methods"><legend><?php echo esc_html(slt_t('booking.payment','Moyen de paiement souhaité')); ?></legend>
<label class="slt-pay-option slt-pay-option--featured"><input type="radio" name="preferred_payment_method" value="Wero" checked><span><strong>Wero</strong><small><?php echo esc_html(slt_t('booking.wero_help','Paiement bancaire européen, si disponible')); ?></small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="Carte bancaire"><span><strong><?php echo esc_html(slt_t('booking.card','Carte bancaire')); ?></strong><small><?php echo esc_html(slt_t('booking.card_help','CB / Visa / Mastercard')); ?></small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="Apple Pay"><span><strong>Apple Pay</strong><small><?php echo esc_html(slt_t('booking.apple_help','Si disponible sur votre appareil')); ?></small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="iDEAL"><span><strong>iDEAL</strong><small><?php echo esc_html(slt_t('booking.ideal_help','Pour les clients néerlandais')); ?></small></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="PayPal"><span><strong>PayPal</strong></span></label>
<label class="slt-pay-option"><input type="radio" name="preferred_payment_method" value="SEPA"><span><strong><?php echo esc_html(slt_t('booking.sepa','Virement SEPA')); ?></strong></span></label>
</fieldset>
<label class="slt-checkbox"><input type="checkbox" name="privacy" value="1" required><span><?php echo esc_html(slt_t('booking.privacy','J’accepte que mes informations soient utilisées pour traiter cette demande de réservation.')); ?></span></label>
<button class="slt-button" type="submit"><?php echo esc_html(slt_t('booking.submit','Créer ma réservation (démo)')); ?></button>
<p class="slt-secure-note"><?php echo esc_html(slt_t('booking.secure','Le paiement Mollie sera connecté dans une étape séparée après validation de ce parcours.')); ?></p>
</form>
</div></aside></div>
<?php get_footer(); ?>
