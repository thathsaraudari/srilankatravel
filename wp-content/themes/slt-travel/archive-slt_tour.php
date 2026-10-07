<?php get_header(); ?>
<section class="page-hero page-hero--simple"><div class="container"><div class="eyebrow">Sri Lanka journeys</div><h1>Find your trip</h1><p>Private itineraries you can use as a starting point and personalise around your dates and interests.</p></div></section>
<section class="section"><div class="container cards-grid">
<?php if(have_posts()):while(have_posts()):the_post();slt_render_tour_card(get_the_ID());endwhile;else: ?><p>No tours have been published yet.</p><?php endif; ?>
</div><div class="container pagination"><?php the_posts_pagination(); ?></div></section>
<?php get_footer(); ?>
