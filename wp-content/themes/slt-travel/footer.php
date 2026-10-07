</main>
<footer class="site-footer"><div class="container footer-grid">
<div>
<a class="brand brand--footer" href="<?php echo esc_url(home_url('/')); ?>"><img class="brand__logo" src="<?php echo esc_url(get_template_directory_uri().'/assets/images/logo-mark.svg'); ?>" width="44" height="44" alt=""><span class="brand__copy"><strong><?php bloginfo('name'); ?></strong><small>Voyages privés au Sri Lanka</small></span></a>
<p>Des circuits privés pensés pour découvrir le Sri Lanka à votre rythme, avec un accompagnement local.</p>
<div class="payment-chips payment-chips--footer"><span>Wero</span><span>CB / Visa / Mastercard</span><span>Apple Pay</span><span>iDEAL</span><span>PayPal</span></div>
</div>
<div><h3>Explorer</h3><?php wp_nav_menu(['theme_location'=>'footer','container'=>false,'fallback_cb'=>false]); ?></div>
<div><h3>Informations</h3><ul class="footer-links"><li><a href="<?php echo esc_url(home_url('/paiement/')); ?>">Paiement sécurisé</a></li><li><a href="<?php echo esc_url(home_url('/conditions-generales/')); ?>">Conditions générales</a></li><li><a href="<?php echo esc_url(home_url('/politique-confidentialite/')); ?>">Confidentialité</a></li><li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></li></ul></div>
</div><div class="container footer-bottom"><span>© <?php echo esc_html((string)date('Y')); ?> <?php bloginfo('name'); ?></span><span>Site de démonstration — informations commerciales à confirmer avant lancement.</span></div></footer>
<?php wp_footer(); ?></body></html>
