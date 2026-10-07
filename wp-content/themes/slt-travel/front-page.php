<?php get_header(); ?>
<section class="hero">
<div class="hero-slideshow" aria-hidden="true">
  <img class="hero-slide hero-slide--1" src="https://images.unsplash.com/photo-1612862862126-865765df2ded?auto=format&fit=crop&w=2200&q=85" alt="">
  <img class="hero-slide hero-slide--2" src="https://images.unsplash.com/photo-1586193804147-64d5c02ef9c1?auto=format&fit=crop&w=2200&q=85" alt="">
  <img class="hero-slide hero-slide--3" src="https://images.unsplash.com/photo-1566650576880-6740b03eaad1?auto=format&fit=crop&w=2200&q=85" alt="">
  <img class="hero-slide hero-slide--4" src="https://images.unsplash.com/photo-1598955890270-d77cdb06d2bb?auto=format&fit=crop&w=2200&q=85" alt="">
  <img class="hero-slide hero-slide--5" src="https://images.unsplash.com/photo-1589373797397-d19670f47549?auto=format&fit=crop&w=2200&q=85" alt="">
</div>
<div class="hero__overlay"></div><div class="container hero__content">
<div class="eyebrow eyebrow--light">Voyages privés au Sri Lanka</div>
<h1>Découvrez le Sri Lanka,<br>à votre façon.</h1>
<p>Culture, safaris, montagnes de thé et plages tropicales — réunis dans des itinéraires privés que vous pouvez personnaliser.</p>
<div class="hero__actions"><a class="button" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Découvrir les circuits</a><a class="button button--ghost" href="<?php echo esc_url(home_url('/contact/')); ?>">Créer mon voyage</a></div>
</div></section>

<section class="trust-strip"><div class="container trust-grid">
<div><strong>Voyages privés</strong><span>À votre rythme</span></div>
<div><strong>Expérience en Europe</strong><span>Service voyageurs depuis la France</span></div>
<div><strong>Partenaires locaux</strong><span>Organisation au Sri Lanka</span></div>
<div><strong>Sur mesure</strong><span>Itinéraires adaptables</span></div>
</div></section>

<section class="section"><div class="container section-heading"><div><div class="eyebrow">Choisissez un itinéraire</div><h2>Des voyages à vivre pleinement</h2></div><p>Partez d’un de nos circuits et adaptez les étapes, les hôtels et les expériences à vos envies.</p></div>
<div class="container cards-grid">
<?php
$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish','meta_query'=>[['key'=>'featured','value'=>'1','compare'=>'=']]]);
if(!$q->have_posts())$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish']);
while($q->have_posts()){ $q->the_post(); slt_render_tour_card(get_the_ID()); } wp_reset_postdata();
?>
</div></section>

<section class="section section--soft"><div class="container"><div class="section-heading"><div><div class="eyebrow">Nos services</div><h2>Un voyage organisé de bout en bout</h2></div><p>Nous combinons préparation depuis l’Europe et coordination locale pour rendre votre séjour simple et fluide.</p></div>
<div class="service-grid">
<a class="service-card" href="<?php echo esc_url(home_url('/nos-services/')); ?>"><span>01</span><h3>Circuits privés</h3><p>Des itinéraires complets entre culture, nature, safari, montagnes et plage.</p></a>
<a class="service-card" href="<?php echo esc_url(home_url('/nos-services/')); ?>"><span>02</span><h3>Voyages sur mesure</h3><p>Dates, rythme, hôtels et expériences adaptés à votre projet.</p></a>
<a class="service-card" href="<?php echo esc_url(home_url('/nos-services/')); ?>"><span>03</span><h3>Transport & chauffeur</h3><p>Transferts aéroport et déplacements privés organisés avec nos partenaires locaux.</p></a>
<a class="service-card" href="<?php echo esc_url(home_url('/nos-services/')); ?>"><span>04</span><h3>Hôtels & expériences</h3><p>Une sélection d’hébergements et d’activités intégrée à votre itinéraire.</p></a>
</div></div></section>

<section class="section"><div class="container split-panel"><div><div class="eyebrow">Pourquoi nous choisir</div><h2>Une expérience voyageur construite entre la France et le Sri Lanka.</h2><p>Notre expérience auprès de voyageurs internationaux en France, notamment à travers Private Cab Transfert, nous a appris l’importance de la ponctualité, d’une communication claire et d’un service fiable. Pour le Sri Lanka, nous appliquons cette même exigence avec des partenaires locaux.</p><a class="text-link" href="<?php echo esc_url(home_url('/a-propos/')); ?>">Découvrir notre histoire →</a></div>
<div class="feature-list"><div><strong>01</strong><span>Interlocuteur clair avant le départ</span></div><div><strong>02</strong><span>Itinéraires privés et flexibles</span></div><div><strong>03</strong><span>Coordination avec des partenaires locaux</span></div><div><strong>04</strong><span>Prix et prestations confirmés avant paiement</span></div></div></div></section>

<section class="section section--soft"><div class="container split-panel"><div><div class="eyebrow">Comment ça marche</div><h2>De votre idée au départ.</h2><p>Choisissez un circuit ou décrivez-nous votre voyage idéal. Nous affinons ensuite l’itinéraire et les prestations avant confirmation.</p><a class="button button--dark" href="<?php echo esc_url(home_url('/comment-ca-marche/')); ?>">Voir les étapes</a></div>
<div class="feature-list"><div><strong>01</strong><span>Choisissez un circuit ou partagez vos envies</span></div><div><strong>02</strong><span>Nous adaptons l’itinéraire et vérifions les disponibilités</span></div><div><strong>03</strong><span>Vous recevez le prix et les prestations confirmées</span></div><div><strong>04</strong><span>Vous réservez et préparez votre départ</span></div></div></div></section>
<?php get_footer(); ?>
