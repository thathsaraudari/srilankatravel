<?php get_header(); the_post();
$id=get_the_ID();$days=(int)slt_field('duration_days',$id,0);$nights=(int)slt_field('duration_nights',$id,max(0,$days-1));
$tagline=(string)slt_field('short_tagline',$id,get_the_excerpt());$highlights=slt_field('highlights',$id,[]);
$itinerary=slt_field('itinerary',$id,[]);$included=slt_field('included',$id,[]);$excluded=slt_field('excluded',$id,[]);
$destinations=get_the_terms($id,'slt_destination'); ?>
<section class="tour-hero"><div class="tour-hero__image"><?php if(has_post_thumbnail())the_post_thumbnail('full'); ?></div><div class="tour-hero__overlay"></div>
<div class="container tour-hero__content"><div class="eyebrow eyebrow--light">Private Sri Lanka journey</div><h1><?php the_title(); ?></h1><?php if($tagline): ?><p><?php echo esc_html($tagline); ?></p><?php endif; ?>
<div class="tour-facts"><?php if($days): ?><span><strong><?php echo esc_html($days); ?></strong> days</span><?php endif; ?><?php if($nights): ?><span><strong><?php echo esc_html($nights); ?></strong> nights</span><?php endif; ?><?php if($destinations&&!is_wp_error($destinations)): ?><span><?php echo esc_html(implode(' · ',wp_list_pluck($destinations,'name'))); ?></span><?php endif; ?></div></div></section>
<div class="container tour-layout"><article class="tour-main">
<section class="tour-section intro-copy"><div class="eyebrow">Overview</div><?php the_content(); ?></section>
<?php if($highlights): ?><section class="tour-section"><div class="eyebrow">Highlights</div><h2>What makes this trip special</h2><div class="highlight-grid"><?php foreach($highlights as $item): ?><div class="highlight-pill">✓ <?php echo esc_html($item['text']??''); ?></div><?php endforeach; ?></div></section><?php endif; ?>
<?php if($itinerary): ?><section class="tour-section" id="itinerary"><div class="eyebrow">Day by day</div><h2>Your itinerary</h2><div class="itinerary">
<?php foreach($itinerary as $row):$hotel=$row['hotel']??null;$meals=$row['meals']??[]; ?><article class="itinerary-day"><div class="itinerary-day__number">Day <?php echo esc_html((string)($row['day']??'')); ?></div><div class="itinerary-day__body"><h3><?php echo esc_html((string)($row['title']??'')); ?></h3>
<?php if(!empty($row['image']))echo wp_get_attachment_image((int)$row['image'],'large',false,['loading'=>'lazy']); ?><div class="prose"><?php echo wp_kses_post((string)($row['description']??'')); ?></div>
<?php if($hotel||$meals): ?><div class="day-meta"><?php if($hotel instanceof WP_Post): ?><span><strong>Hotel:</strong> <?php echo esc_html($hotel->post_title); ?></span><?php endif; ?><?php if($meals): ?><span><strong>Meals:</strong> <?php echo esc_html(implode(', ',array_map('ucfirst',$meals))); ?></span><?php endif; ?></div><?php endif; ?>
</div></article><?php endforeach; ?></div></section><?php endif; ?>
<?php if($included||$excluded): ?><section class="tour-section inclusions-grid"><?php if($included): ?><div><div class="eyebrow">Included</div><h2>What's included</h2><ul class="check-list"><?php foreach($included as $row): ?><li>✓ <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?><?php if($excluded): ?><div><div class="eyebrow">Good to know</div><h2>Not included</h2><ul class="cross-list"><?php foreach($excluded as $row): ?><li>– <?php echo esc_html($row['item']??''); ?></li><?php endforeach; ?></ul></div><?php endif; ?></section><?php endif; ?>
</article><aside class="tour-sidebar"><div class="quote-card"><div class="eyebrow">Your trip</div><div class="quote-card__price"><?php echo esc_html(slt_price_label($id)); ?></div><p>Tell us your dates and travellers. We’ll confirm availability and prepare your personalised quote.</p><?php echo do_shortcode('[slt_enquiry_form tour_id="'.$id.'"]'); ?></div></aside></div>
<?php get_footer(); ?>
