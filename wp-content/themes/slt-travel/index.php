<?php get_header(); ?>
<section class="section"><div class="container narrow">
<?php if(have_posts()):while(have_posts()):the_post(); ?><article><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article><?php endwhile;else: ?><p>Nothing found.</p><?php endif; ?>
</div></section>
<?php get_footer(); ?>
