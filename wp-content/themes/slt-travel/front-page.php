<?php get_header(); ?>
<section class="hero"><div class="hero__overlay"></div><div class="container hero__content">
<div class="eyebrow eyebrow--light">Private journeys across Sri Lanka</div>
<h1>Discover Sri Lanka,<br>your way.</h1>
<p>Culture, wildlife, tea country and tropical beaches — thoughtfully combined into private journeys you can personalise.</p>
<div class="hero__actions"><a class="button" href="<?php echo esc_url(get_post_type_archive_link('slt_tour')); ?>">Explore our trips</a><a class="button button--ghost" href="<?php echo esc_url(home_url('/contact/')); ?>">Create my trip</a></div>
</div></section>
<section class="section"><div class="container section-heading"><div><div class="eyebrow">Start with an itinerary</div><h2>Trips worth travelling for</h2></div><p>Use one of our journeys as it is, or ask us to adapt the route, hotels and experiences around you.</p></div>
<div class="container cards-grid">
<?php
$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish','meta_query'=>function_exists('get_field')?[['key'=>'featured','value'=>'1','compare'=>'=']]:[]]);
if(!$q->have_posts())$q=new WP_Query(['post_type'=>'slt_tour','posts_per_page'=>6,'post_status'=>'publish']);
while($q->have_posts()){ $q->the_post(); slt_render_tour_card(get_the_ID()); } wp_reset_postdata();
?>
</div></section>
<section class="section section--soft"><div class="container split-panel"><div><div class="eyebrow">Tailor-made</div><h2>Have something different in mind?</h2><p>Tell us your dates, group size and interests. We’ll use them to shape a Sri Lanka itinerary around your trip.</p><a class="button button--dark" href="<?php echo esc_url(home_url('/contact/')); ?>">Plan a custom journey</a></div>
<div class="feature-list"><div><strong>01</strong><span>Tell us what you enjoy</span></div><div><strong>02</strong><span>Receive a personalised proposal</span></div><div><strong>03</strong><span>Refine it until it feels right</span></div></div></div></section>
<?php get_footer(); ?>
