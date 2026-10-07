<?php get_header(); ?>
<section class="page-hero page-hero--simple"><div class="container"><div class="eyebrow">Circuits au Sri Lanka</div><h1>Trouvez votre voyage</h1><p>Des itinéraires privés conçus comme point de départ, à personnaliser selon vos dates, votre rythme et vos envies.</p></div></section>
<section class="section"><div class="container cards-grid">
<?php if(have_posts()):while(have_posts()):the_post();slt_render_tour_card(get_the_ID());endwhile;else: ?><p>Aucun circuit n’est encore publié.</p><?php endif; ?>
</div><div class="container pagination"><?php the_posts_pagination(); ?></div></section>
<?php get_footer(); ?>
