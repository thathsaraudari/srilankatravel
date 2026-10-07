<?php get_header(); ?>
<section class="hero"><div class="hero__overlay"></div><div class="container hero__content">
<div class="eyebrow eyebrow--light">Circuits privés · Sri Lanka</div>
<h1>Le Sri Lanka,<br>à votre rythme.</h1>
<p>Patrimoine, safaris, plantations de thé et plages tropicales réunis dans des voyages privés conçus pour les voyageurs européens.</p>
<div class="hero__actions"><a class="button" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Voir les circuits</a><a class="button button--ghost" href="<?php echo esc_url(home_url('/pourquoi-nous/')); ?>">Pourquoi voyager avec nous</a></div>
</div></section>

<section class="trust-strip"><div class="container trust-strip__inner"><span>✓ Circuits privés</span><span>✓ Assistance locale</span><span>✓ Paiement européen sécurisé</span><span>✓ Acompte configurable</span></div></section>

<section class="section"><div class="container section-heading"><div><div class="eyebrow">Choisissez votre voyage</div><h2>Nos circuits au Sri Lanka</h2></div><p>Chaque itinéraire sert de base. Les hôtels, étapes et activités peuvent ensuite être adaptés avant confirmation finale.</p></div>
<div class="container cards-grid">
<?php
$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish','meta_query'=>[['key'=>'featured','value'=>'1','compare'=>'=']]]);
if(!$q->have_posts())$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish']);
while($q->have_posts()){ $q->the_post(); slt_render_tour_card(get_the_ID()); } wp_reset_postdata();
?>
</div></section>

<section class="section section--image-story"><div class="container image-story">
<div class="image-story__image"><img src="<?php echo esc_url(get_template_directory_uri().'/assets/images/dambulla.webp'); ?>" alt="Ambiance culturelle du Sri Lanka" loading="lazy"></div>
<div class="image-story__copy"><div class="eyebrow">Un voyage complet</div><h2>Culture, nature et océan</h2><p>Notre circuit de démonstration relie le Triangle culturel, Kandy, les collines de Nuwara Eliya et la côte sud-ouest dans un seul voyage privé.</p><a class="text-link text-link--large" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Découvrir l’itinéraire →</a></div>
</div></section>

<section class="section section--soft"><div class="container split-panel"><div><div class="eyebrow">Réservation</div><h2>Choisissez votre circuit, puis réservez.</h2><p>Le site est construit autour d’un vrai parcours de réservation. En mode démonstration aucun montant n’est débité ; quand Mollie sera connecté, le même parcours redirigera vers un paiement sécurisé.</p><a class="button button--dark" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Commencer une réservation</a></div>
<div class="feature-list"><div><strong>01</strong><span>Sélectionnez le circuit et la date</span></div><div><strong>02</strong><span>Choisissez Wero, carte, Apple Pay, iDEAL ou PayPal</span></div><div><strong>03</strong><span>Payez l’acompte via Mollie lorsque le compte sera connecté</span></div></div></div></section>

<section class="section payment-section"><div class="container"><div class="section-heading"><div><div class="eyebrow">Paiements européens</div><h2>Des moyens de paiement familiers</h2></div><p>Le checkout est préparé pour privilégier les méthodes adaptées à la France et à l’Europe, tout en gardant iDEAL disponible pour les voyageurs néerlandais.</p></div><div class="payment-chips payment-chips--large"><span>Wero</span><span>Carte bancaire</span><span>Visa</span><span>Mastercard</span><span>Apple Pay</span><span>iDEAL</span><span>PayPal</span><span>SEPA</span></div></div></section>
<?php get_footer(); ?>
