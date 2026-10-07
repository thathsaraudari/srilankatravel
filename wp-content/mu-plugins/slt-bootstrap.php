<?php
/**
 * Plugin Name: SLT Bootstrap
 * Description: Automatic setup and demo content for the Sri Lanka Travel site.
 * Version: 0.5.0
 */
if (!defined('ABSPATH')) exit;

add_action('init', function (): void {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $plugin='slt-core/slt-core.php';
    if(!class_exists('SLT_Core')&&file_exists(WP_PLUGIN_DIR.'/slt-core/slt-core.php'))require_once WP_PLUGIN_DIR.'/slt-core/slt-core.php';
    if(!is_plugin_active($plugin)&&class_exists('SLT_Core')){
        $result=activate_plugin($plugin);
        if(is_wp_error($result))update_option('slt_bootstrap_error',$result->get_error_message(),false);
    }

    $theme=wp_get_theme('slt-travel');
    if($theme->exists()&&get_stylesheet()!=='slt-travel')switch_theme('slt-travel');
    if(get_option('slt_bootstrap_completed_v5'))return;

    $upsert_page=function(string $slug,string $title,string $content): int {
        $page=get_page_by_path($slug);
        $data=['post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>$content];
        if($page){$data['ID']=$page->ID;$id=wp_update_post($data,true);}else{$id=wp_insert_post($data,true);}
        return is_wp_error($id)?0:(int)$id;
    };

    $pages=[];
    $pages['a-propos']=$upsert_page('a-propos','À propos','
<p class="content-lead">Nous créons des voyages privés au Sri Lanka avec un objectif simple : proposer un itinéraire clair, flexible et facile à réserver depuis l’Europe.</p>
<div class="info-grid">
<div class="info-card"><h3>Connaissance locale</h3><p>Les circuits sont conçus autour d’étapes, d’hôtels et d’expériences réellement adaptées au voyage au Sri Lanka.</p></div>
<div class="info-card"><h3>Voyage privé</h3><p>Vous voyagez à votre rythme. Les itinéraires servent de base et peuvent évoluer avant la confirmation finale.</p></div>
<div class="info-card"><h3>Accompagnement</h3><p>Une réservation centralisée et un interlocuteur pour coordonner le séjour avec les partenaires locaux.</p></div>
</div>
<h2>Notre approche</h2>
<p>Nous privilégions des parcours équilibrés : patrimoine culturel, nature, paysages des montagnes et temps de repos sur la côte. L’objectif n’est pas de multiplier les étapes, mais de construire un voyage cohérent.</p>
<p><strong>Important :</strong> le nom juridique de l’entreprise, son adresse et ses informations d’immatriculation seront ajoutés avant l’ouverture commerciale du site.</p>');

    $pages['pourquoi-nous']=$upsert_page('pourquoi-nous','Pourquoi nous','
<p class="content-lead">Un voyage au Sri Lanka doit rester simple à comprendre avant le départ et agréable une fois sur place.</p>
<div class="info-grid">
<div class="info-card"><h3>Itinéraires transparents</h3><p>Chaque journée affiche les principales visites, l’hébergement prévu et les repas inclus.</p></div>
<div class="info-card"><h3>Réservation européenne</h3><p>Le paiement est préparé pour Mollie avec des moyens familiers en Europe : Wero lorsqu’il est disponible, carte bancaire, Apple Pay, iDEAL, PayPal et SEPA.</p></div>
<div class="info-card"><h3>Flexibilité</h3><p>Dates, hôtels et certaines activités peuvent être adaptés avant la confirmation définitive.</p></div>
</div>
<h2>Du projet au départ</h2>
<p>Choisissez un circuit, indiquez vos dates et voyageurs, puis lancez la réservation. Lorsque le tarif est confirmé, l’acompte sécurise la réservation. Le solde et les détails pratiques sont ensuite suivis dans le dossier de réservation.</p>');

    $pages['faq']=$upsert_page('faq','Questions fréquentes','
<div class="faq-list">
<details open><summary>Les circuits sont-ils privés ?</summary><p>Oui. Les itinéraires présentés sont conçus comme des voyages privés et peuvent être adaptés selon les disponibilités.</p></details>
<details><summary>Puis-je modifier les hôtels ou certaines étapes ?</summary><p>Oui. Les hôtels et activités indiqués constituent une proposition. Les changements sont confirmés avant paiement définitif.</p></details>
<details><summary>Comment fonctionne l’acompte ?</summary><p>Le pourcentage d’acompte est défini dans les paramètres du site. Le montant exact est affiché avant le paiement.</p></details>
<details><summary>Quels moyens de paiement acceptez-vous ?</summary><p>Le checkout est conçu pour Mollie : Wero lorsqu’il est disponible pour le client et le marchand, carte bancaire, Apple Pay, iDEAL, PayPal et virement SEPA.</p></details>
<details><summary>Le paiement est-il déjà actif sur ce site de démonstration ?</summary><p>Non. Le site fonctionne actuellement en mode démonstration : vous pouvez tester le parcours, mais aucun montant réel n’est débité.</p></details>
<details><summary>Les vols internationaux sont-ils inclus ?</summary><p>Sauf indication contraire dans le circuit, les vols internationaux ne sont pas inclus.</p></details>
<details><summary>Que se passe-t-il après la réservation ?</summary><p>La réservation apparaît dans le tableau de bord. L’équipe peut ensuite confirmer le tarif, le paiement, les hôtels et les détails du voyage.</p></details>
</div>');

    $pages['contact']=$upsert_page('contact','Contact','
<p class="content-lead">Une question sur un circuit, une réservation ou votre prochain voyage au Sri Lanka ? Retrouvez ici les coordonnées de l’équipe.</p>
[slt_contact_details]
<h2>Avant de nous contacter</h2>
<p>Pour réserver un circuit, utilisez directement le bouton <strong>Réserver</strong> sur la page du voyage. Cela permet de conserver les dates, le nombre de voyageurs et le statut du paiement dans le même dossier.</p>');

    $pages['paiement']=$upsert_page('paiement','Paiement sécurisé','
<p class="content-lead">Le site est préparé pour utiliser Mollie comme plateforme de paiement européenne.</p>
<div class="info-grid">
<div class="info-card"><h3>Wero</h3><p>Présenté en priorité lorsqu’il est activé et disponible pour le pays du client et le profil marchand.</p></div>
<div class="info-card"><h3>Carte bancaire</h3><p>Carte bancaire, Visa et Mastercard constituent l’option principale pour les voyageurs en France lorsque Wero n’est pas disponible.</p></div>
<div class="info-card"><h3>Moyens locaux</h3><p>Apple Pay, iDEAL pour les Pays-Bas, PayPal et virement SEPA peuvent compléter le checkout selon les méthodes activées dans Mollie.</p></div>
</div>
<h2>Comment cela fonctionne</h2>
<p>Lorsque le mode de paiement réel sera activé, les informations bancaires ne seront pas saisies ni stockées par ce site. Le client sera redirigé vers le checkout sécurisé de Mollie pour autoriser son paiement, puis reviendra sur la page de réservation.</p>
<p class="legal-note"><strong>Mode démo :</strong> aucun paiement réel n’est actuellement effectué. Une clé API Mollie de test ou de production devra être ajoutée dans <em>Site Settings</em> avant activation.</p>');

    $pages['reservation']=$upsert_page('reservation','Votre réservation','[slt_booking_result]');

    $pages['conditions-generales']=$upsert_page('conditions-generales','Conditions générales','
<div class="legal-note"><strong>Brouillon de démonstration.</strong> Cette page doit être relue et complétée avec les coordonnées légales de l’entreprise, les règles d’annulation, les responsabilités et les conditions de remboursement avant toute vente réelle.</div>
<h2>1. Objet</h2><p>Les présentes conditions encadrent la réservation de prestations de voyage présentées sur ce site.</p>
<h2>2. Formation de la réservation</h2><p>Une réservation n’est définitivement confirmée qu’après validation des disponibilités, acceptation du prix final et réception du paiement demandé.</p>
<h2>3. Prix et acompte</h2><p>Les prix affichés ou communiqués précisent les prestations incluses. Un acompte peut être demandé pour confirmer le dossier. Le pourcentage et le montant sont communiqués avant paiement.</p>
<h2>4. Modification et annulation</h2><p>Les règles précises de modification, d’annulation et de remboursement doivent être ajoutées ici en fonction des contrats conclus avec les agences et prestataires locaux.</p>
<h2>5. Prestataires locaux</h2><p>Les prestations sur place peuvent être exécutées par des partenaires locaux. Le rôle contractuel exact de l’entreprise exploitant ce site doit être décrit avant lancement.</p>
<h2>6. Droit applicable et médiation</h2><p>À compléter avec le droit applicable, le tribunal compétent et, le cas échéant, les informations de médiation imposées au professionnel établi en France.</p>');

    $pages['politique-confidentialite']=$upsert_page('politique-confidentialite','Politique de confidentialité','
<div class="legal-note"><strong>Brouillon de démonstration.</strong> Compléter l’identité et les coordonnées du responsable de traitement avant le lancement commercial.</div>
<h2>Données collectées</h2><p>Lors d’une réservation, le site peut collecter notamment le nom, l’adresse e-mail, le téléphone, la date de voyage, le nombre de voyageurs et les informations nécessaires au suivi du dossier.</p>
<h2>Finalités</h2><p>Ces informations sont utilisées pour traiter la réservation, communiquer avec le voyageur, organiser les prestations demandées et respecter les obligations administratives applicables.</p>
<h2>Paiement</h2><p>Lorsque Mollie sera activé, les données de paiement sensibles seront traitées par Mollie dans son environnement sécurisé. Le site conserve uniquement les références et statuts nécessaires au suivi de la réservation.</p>
<h2>Durée et droits</h2><p>La durée de conservation ainsi que l’adresse permettant d’exercer les droits d’accès, de rectification, d’effacement et d’opposition doivent être finalisées avant lancement.</p>
<h2>Cookies</h2><p>Une solution de gestion du consentement devra être activée avant l’ajout d’outils d’analyse ou de marketing nécessitant un consentement.</p>');

    $hotel_data=[
        'Cinnamon Lodge'=>['location'=>'Habarana','rating'=>'5'],
        "Earl's Regency"=>['location'=>'Kandy','rating'=>'5'],
        'Jetwing St Andrews'=>['location'=>'Nuwara Eliya','rating'=>'5'],
        'Cinnamon Bey'=>['location'=>'Beruwala','rating'=>'5']
    ];
    $hotels=[];
    foreach($hotel_data as $name=>$data){
        $existing=get_page_by_title($name,OBJECT,'slt_hotel');
        $id=$existing?$existing->ID:wp_insert_post(['post_type'=>'slt_hotel','post_status'=>'publish','post_title'=>$name]);
        if(!is_wp_error($id)&&$id){update_post_meta($id,'location',$data['location']);update_post_meta($id,'star_rating',$data['rating']);$hotels[$name]=(int)$id;}
    }

    $terms=[];
    foreach(['Habarana','Dambulla','Sigiriya','Kandy','Nuwara Eliya','Beruwala','Balapitiya','Kosgoda'] as $destination){
        $term=term_exists($destination,'slt_destination');if(!$term)$term=wp_insert_term($destination,'slt_destination');
        if(!is_wp_error($term))$terms[]=(int)(is_array($term)?$term['term_id']:$term);
    }
    foreach(['Culture','Safari','Pays du thé','Plage'] as $style)if(!term_exists($style,'slt_travel_style'))wp_insert_term($style,'slt_travel_style');

    $demo=get_page_by_title('Découverte du Sri Lanka – 7 jours',OBJECT,'slt_tour');
    if(!$demo)$demo=get_page_by_title('Sri Lanka Highlights – 7 Days',OBJECT,'slt_tour');
    if(!$demo){$existing=get_posts(['post_type'=>'slt_tour','post_status'=>'any','posts_per_page'=>2]);if(count($existing)===1)$demo=$existing[0];}

    $tour_data=[
        'post_type'=>'slt_tour','post_status'=>'publish','post_title'=>'Découverte du Sri Lanka – 7 jours',
        'post_excerpt'=>'Un voyage privé entre patrimoine, safari, montagnes de thé et côte sud-ouest.',
        'post_content'=>'<p>Commencez au cœur du Triangle culturel, poursuivez vers Kandy et les paysages frais de Nuwara Eliya, puis terminez votre voyage au bord de l’océan Indien à Beruwala.</p><p>Cet itinéraire est une base : les dates, les hôtels et les expériences peuvent être adaptés avant confirmation.</p>'
    ];
    if($demo){$tour_data['ID']=$demo->ID;$tour_id=wp_update_post($tour_data,true);}else{$tour_id=wp_insert_post($tour_data,true);}

    if(!is_wp_error($tour_id)&&$tour_id){
        update_post_meta($tour_id,'_slt_seeded_demo',1);update_post_meta($tour_id,'duration_days',7);update_post_meta($tour_id,'duration_nights',6);
        update_post_meta($tour_id,'price_basis','request');update_post_meta($tour_id,'featured',1);
        update_post_meta($tour_id,'short_tagline','Culture, safari, pays du thé et plage dans un même voyage privé.');
        update_post_meta($tour_id,'highlights',[
            ['text'=>'Temple troglodyte de Dambulla'],['text'=>'Forteresse de Sigiriya'],['text'=>'Safari au parc national de Kaudulla'],
            ['text'=>'Spectacle culturel kandyen'],['text'=>'Temple de la Dent'],['text'=>'Ramboda Falls et plantations de thé'],
            ['text'=>'Plage de Beruwala'],['text'=>'Rivière Madu et mangroves']
        ]);
        update_post_meta($tour_id,'itinerary',[
            ['day'=>1,'title'=>'Aéroport → Habarana','description'=>'Arrivée au Sri Lanka et route vers Habarana. Après l’installation à l’hôtel, visite du temple troglodyte de Dambulla, classé au patrimoine mondial de l’UNESCO.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['dinner']],
            ['day'=>2,'title'=>'Sigiriya & safari à Kaudulla','description'=>'Visite de la forteresse de Sigiriya, puis safari en fin de journée au parc national de Kaudulla, particulièrement connu pour ses éléphants.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['breakfast','dinner']],
            ['day'=>3,'title'=>'Habarana → Kandy','description'=>'Route vers Kandy avec arrêt dans un jardin d’épices à Matale. Découverte de l’artisanat traditionnel puis spectacle de danses kandiennes.','hotel_id'=>$hotels["Earl's Regency"]??0,'meals'=>['breakfast','dinner']],
            ['day'=>4,'title'=>'Kandy → Nuwara Eliya','description'=>'Visite du Temple de la Dent puis route vers les montagnes via les chutes de Ramboda. Découverte d’une plantation et d’une fabrique de thé.','hotel_id'=>$hotels['Jetwing St Andrews']??0,'meals'=>['breakfast','dinner']],
            ['day'=>5,'title'=>'Nuwara Eliya → Beruwala','description'=>'Départ des montagnes vers Beruwala sur la côte sud-ouest. Installation à l’hôtel et temps libre au bord de la plage.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
            ['day'=>6,'title'=>'Rivière Madu & Kosgoda','description'=>'Exploration de la rivière Madu et de ses mangroves à Balapitiya, puis visite d’un centre de protection des tortues à Kosgoda.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
            ['day'=>7,'title'=>'Beruwala → Aéroport','description'=>'Selon l’horaire du vol retour, profitez de l’hôtel avant le départ vers l’aéroport.','hotel_id'=>0,'meals'=>['breakfast']]
        ]);
        update_post_meta($tour_id,'included',[
            ['item'=>'6 nuits dans les hôtels indiqués ou des établissements équivalents confirmés'],
            ['item'=>'Les repas indiqués pour chaque journée'],
            ['item'=>'Organisation du circuit privé selon la proposition confirmée']
        ]);
        update_post_meta($tour_id,'excluded',[
            ['item'=>'Vols internationaux'],['item'=>'Dépenses personnelles'],['item'=>'Prestations non confirmées dans la réservation finale']
        ]);
        wp_set_object_terms($tour_id,$terms,'slt_destination');wp_set_object_terms($tour_id,['Culture','Safari','Pays du thé','Plage'],'slt_travel_style');
    }

    $make_menu=function(string $name,array $entries): int {
        $menu=wp_get_nav_menu_object($name);$menu_id=$menu?(int)$menu->term_id:wp_create_nav_menu($name);
        if(is_wp_error($menu_id))return 0;
        foreach((wp_get_nav_menu_items($menu_id)?:[]) as $item)wp_delete_post($item->ID,true);
        foreach($entries as $entry){
            if(isset($entry['page'])&&$entry['page']){
                wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$entry['title'],'menu-item-object-id'=>(int)$entry['page'],'menu-item-object'=>'page','menu-item-status'=>'publish','menu-item-type'=>'post_type']);
            }else{
                wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$entry['title'],'menu-item-url'=>$entry['url'],'menu-item-status'=>'publish','menu-item-type'=>'custom']);
            }
        }
        return (int)$menu_id;
    };

    $primary=$make_menu('Primary',[
        ['title'=>'Accueil','url'=>home_url('/')],['title'=>'Circuits','url'=>get_post_type_archive_link('slt_tour')?:home_url('/tours/')],
        ['title'=>'À propos','page'=>$pages['a-propos']],['title'=>'Pourquoi nous','page'=>$pages['pourquoi-nous']],
        ['title'=>'FAQ','page'=>$pages['faq']],['title'=>'Contact','page'=>$pages['contact']]
    ]);
    $footer=$make_menu('Footer',[
        ['title'=>'À propos','page'=>$pages['a-propos']],['title'=>'FAQ','page'=>$pages['faq']],['title'=>'Paiement','page'=>$pages['paiement']],
        ['title'=>'Conditions générales','page'=>$pages['conditions-generales']],['title'=>'Confidentialité','page'=>$pages['politique-confidentialite']],['title'=>'Contact','page'=>$pages['contact']]
    ]);
    $locations=get_theme_mod('nav_menu_locations',[]);if($primary)$locations['primary']=$primary;if($footer)$locations['footer']=$footer;set_theme_mod('nav_menu_locations',$locations);

    $settings=get_option('slt_settings',[]);
    if(!is_array($settings))$settings=[];
    $settings['currency']='EUR';
    if(empty($settings['deposit_percent']))$settings['deposit_percent']=30;
    if(empty($settings['payment_mode'])||$settings['payment_mode']==='enquiry'||$settings['payment_mode']==='woocommerce')$settings['payment_mode']='demo';
    update_option('slt_settings',$settings);

    flush_rewrite_rules(false);
    update_option('slt_bootstrap_completed_v5',1,false);
    delete_option('slt_bootstrap_error');
},50);
