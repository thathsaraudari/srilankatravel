<?php get_header(); ?>
<section class="hero"><div class="hero__overlay"></div><div class="container hero__content">
<div class="eyebrow eyebrow--light">Voyages privés au Sri Lanka</div>
<h1>Découvrez le Sri Lanka,<br>à votre façon.</h1>
<p>Culture, safaris, montagnes de thé et plages tropicales — réunis dans des itinéraires privés que vous pouvez personnaliser.</p>
<div class="hero__actions"><a class="button" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Découvrir les circuits</a><a class="button button--ghost" href="<?php echo esc_url(home_url('/contact/')); ?>">Créer mon voyage</a></div>
</div></section>
<section class="section"><div class="container section-heading"><div><div class="eyebrow">Choisissez un itinéraire</div><h2>Des voyages à vivre pleinement</h2></div><p>Partez d’un de nos circuits et adaptez les étapes, les hôtels et les expériences à vos envies.</p></div>
<div class="container cards-grid">
<?php
$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish','meta_query'=>[['key'=>'featured','value'=>'1','compare'=>'=']]]);
if(!$q->have_posts())$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish']);
while($q->have_posts()){ $q->the_post(); slt_render_tour_card(get_the_ID()); } wp_reset_postdata();
?>
</div></section>
<section class="section section--soft"><div class="container split-panel"><div><div class="eyebrow">Sur mesure</div><h2>Vous imaginez un autre voyage ?</h2><p>Indiquez vos dates, le nombre de voyageurs et ce que vous aimez. Nous construirons un itinéraire au Sri Lanka autour de votre projet.</p><a class="button button--dark" href="<?php echo esc_url(home_url('/contact/')); ?>">Créer un voyage sur mesure</a></div>
<div class="feature-list"><div><strong>01</strong><span>Partagez vos envies</span></div><div><strong>02</strong><span>Recevez une proposition personnalisée</span></div><div><strong>03</strong><span>Ajustez-la jusqu’à ce qu’elle vous corresponde</span></div></div></div></section>
<?php get_footer(); ?>
