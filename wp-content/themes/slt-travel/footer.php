</main>
<footer class="site-footer"><div class="container footer-grid">
<div><div class="brand brand--footer"><span class="brand__mark">SL</span><span><?php bloginfo('name'); ?></span></div><p>Des voyages privés au Sri Lanka, pensés avec une vraie connaissance locale.</p></div>
<div><h3>Explorer</h3><?php wp_nav_menu(['theme_location'=>'footer','container'=>false,'fallback_cb'=>false]); ?></div>
<div><h3>Contact</h3><p><a href="<?php echo esc_url(home_url('/contact/')); ?>">Demander un voyage personnalisé</a></p></div>
</div><div class="container footer-bottom">© <?php echo esc_html((string)date('Y')); ?> <?php bloginfo('name'); ?></div></footer>
<?php wp_footer(); ?></body></html>
