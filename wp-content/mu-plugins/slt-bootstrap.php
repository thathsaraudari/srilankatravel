<?php
/**
 * Plugin Name: SLT Bootstrap
 * Description: One-time setup and demo content for the Sri Lanka Travel site.
 * Version: 0.4.0
 */
if (!defined('ABSPATH')) exit;

add_action('init', function (): void {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $plugin='slt-core/slt-core.php';
    if (!class_exists('SLT_Core') && file_exists(WP_PLUGIN_DIR.'/slt-core/slt-core.php')) {
        require_once WP_PLUGIN_DIR.'/slt-core/slt-core.php';
    }
    if (!is_plugin_active($plugin) && class_exists('SLT_Core')) {
        $result=activate_plugin($plugin);
        if (is_wp_error($result)) update_option('slt_bootstrap_error',$result->get_error_message(),false);
    }

    $theme=wp_get_theme('slt-travel');
    if ($theme->exists() && get_stylesheet()!=='slt-travel') switch_theme('slt-travel');

    if (get_option('slt_bootstrap_completed_v4')) return;

    $contact=get_page_by_path('contact');
    if ($contact) {
        $contact_id=$contact->ID;
        wp_update_post([
            'ID'=>$contact_id,
            'post_title'=>'Contact',
            'post_content'=>'<p>Indiquez-nous vos dates, le nombre de voyageurs et ce que vous aimeriez découvrir au Sri Lanka.</p>[slt_enquiry_form tour_id="0"]'
        ]);
    } else {
        $contact_id=wp_insert_post([
            'post_type'=>'page','post_status'=>'publish','post_title'=>'Contact','post_name'=>'contact',
            'post_content'=>'<p>Indiquez-nous vos dates, le nombre de voyageurs et ce que vous aimeriez découvrir au Sri Lanka.</p>[slt_enquiry_form tour_id="0"]'
        ]);
    }

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
        if(!is_wp_error($id)&&$id){
            update_post_meta($id,'location',$data['location']);
            update_post_meta($id,'star_rating',$data['rating']);
            $hotels[$name]=(int)$id;
        }
    }

    $terms=[];
    foreach(['Habarana','Dambulla','Sigiriya','Kandy','Nuwara Eliya','Beruwala','Balapitiya','Kosgoda'] as $destination){
        $term=term_exists($destination,'slt_destination');
        if(!$term)$term=wp_insert_term($destination,'slt_destination');
        if(!is_wp_error($term))$terms[]=(int)(is_array($term)?$term['term_id']:$term);
    }
    foreach(['Culture','Safari','Pays du thé','Plage'] as $style){
        if(!term_exists($style,'slt_travel_style'))wp_insert_term($style,'slt_travel_style');
    }

    $demo=get_page_by_title('Découverte du Sri Lanka – 7 jours',OBJECT,'slt_tour');
    if(!$demo)$demo=get_page_by_title('Sri Lanka Highlights – 7 Days',OBJECT,'slt_tour');
    if(!$demo){
        $existing_tours=get_posts(['post_type'=>'slt_tour','post_status'=>'any','posts_per_page'=>2]);
        if(count($existing_tours)===1)$demo=$existing_tours[0];
    }

    if($demo){
        $tour_id=$demo->ID;
        wp_update_post([
            'ID'=>$tour_id,
            'post_title'=>'Découverte du Sri Lanka – 7 jours',
            'post_excerpt'=>'Un voyage privé entre patrimoine, safari, montagnes de thé et côte sud-ouest.',
            'post_content'=>'<p>Commencez au cœur du Triangle culturel, poursuivez vers Kandy et les paysages frais de Nuwara Eliya, puis terminez votre voyage au bord de l’océan Indien à Beruwala.</p><p>Cet itinéraire est une base : les dates, les hôtels et les expériences peuvent être adaptés à votre projet.</p>'
        ]);
    } else {
        $tour_id=wp_insert_post([
            'post_type'=>'slt_tour','post_status'=>'publish','post_title'=>'Découverte du Sri Lanka – 7 jours',
            'post_excerpt'=>'Un voyage privé entre patrimoine, safari, montagnes de thé et côte sud-ouest.',
            'post_content'=>'<p>Commencez au cœur du Triangle culturel, poursuivez vers Kandy et les paysages frais de Nuwara Eliya, puis terminez votre voyage au bord de l’océan Indien à Beruwala.</p><p>Cet itinéraire est une base : les dates, les hôtels et les expériences peuvent être adaptés à votre projet.</p>'
        ]);
    }

    if(!is_wp_error($tour_id)&&$tour_id){
        update_post_meta($tour_id,'_slt_seeded_demo',1);
        update_post_meta($tour_id,'duration_days',7);
        update_post_meta($tour_id,'duration_nights',6);
        update_post_meta($tour_id,'price_basis','request');
        update_post_meta($tour_id,'featured',1);
        update_post_meta($tour_id,'short_tagline','Culture, safari, pays du thé et plage dans un même voyage privé.');
        update_post_meta($tour_id,'highlights',[
            ['text'=>'Temple troglodyte de Dambulla'],
            ['text'=>'Forteresse de Sigiriya'],
            ['text'=>'Safari au parc national de Kaudulla'],
            ['text'=>'Spectacle culturel kandyen'],
            ['text'=>'Temple de la Dent'],
            ['text'=>'Ramboda Falls et plantations de thé'],
            ['text'=>'Plage de Beruwala'],
            ['text'=>'Rivière Madu et centre de protection des tortues de Kosgoda']
        ]);
        update_post_meta($tour_id,'itinerary',[
            ['day'=>1,'title'=>'Aéroport → Habarana','description'=>'Arrivée au Sri Lanka et route vers Habarana. Après l’installation à l’hôtel et un moment de repos, visite du temple troglodyte de Dambulla, classé au patrimoine mondial de l’UNESCO. Retour à l’hôtel pour le dîner.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['dinner']],
            ['day'=>2,'title'=>'Sigiriya & safari à Kaudulla','description'=>'Après le petit-déjeuner, visite de la forteresse de Sigiriya, ancienne citadelle royale associée au roi Kasyapa. Retour à l’hôtel pour vous détendre, puis safari en fin de journée au parc national de Kaudulla, particulièrement connu pour ses éléphants.','hotel_id'=>$hotels['Cinnamon Lodge']??0,'meals'=>['breakfast','dinner']],
            ['day'=>3,'title'=>'Habarana → Kandy','description'=>'Départ vers Kandy après le petit-déjeuner. Arrêt dans un jardin d’épices à Matale, puis continuation vers Kandy. Plus tard, découverte de l’artisanat traditionnel et spectacle de danses kandiennes.','hotel_id'=>$hotels["Earl's Regency"]??0,'meals'=>['breakfast','dinner']],
            ['day'=>4,'title'=>'Kandy → Nuwara Eliya','description'=>'Visite du Temple de la Dent à Kandy, puis route vers les montagnes en passant par les chutes de Ramboda. À Nuwara Eliya, découverte de la culture et de la fabrication du thé de Ceylan dans une plantation et une fabrique.','hotel_id'=>$hotels['Jetwing St Andrews']??0,'meals'=>['breakfast','dinner']],
            ['day'=>5,'title'=>'Nuwara Eliya → Beruwala','description'=>'Après le petit-déjeuner, départ des montagnes vers Beruwala sur la côte sud-ouest. Installation à l’hôtel et soirée libre au bord de la plage.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
            ['day'=>6,'title'=>'Rivière Madu & Kosgoda','description'=>'Exploration de la rivière Madu et de ses mangroves à Balapitiya, puis visite d’un centre de protection des tortues à Kosgoda. Retour à Beruwala pour la dernière nuit du circuit.','hotel_id'=>$hotels['Cinnamon Bey']??0,'meals'=>['breakfast','dinner']],
            ['day'=>7,'title'=>'Beruwala → Aéroport','description'=>'Selon l’horaire du vol retour, profitez de l’hôtel avant le départ vers l’aéroport.','hotel_id'=>0,'meals'=>['breakfast']]
        ]);
        update_post_meta($tour_id,'included',[
            ['item'=>'6 nuits dans les hôtels indiqués dans l’itinéraire ou dans des établissements équivalents confirmés'],
            ['item'=>'Les repas indiqués pour chaque journée']
        ]);
        update_post_meta($tour_id,'excluded',[
            ['item'=>'Vols internationaux'],
            ['item'=>'Dépenses personnelles et prestations non confirmées dans le devis final']
        ]);
        wp_set_object_terms($tour_id,$terms,'slt_destination');
        wp_set_object_terms($tour_id,['Culture','Safari','Pays du thé','Plage'],'slt_travel_style');
    }

    $locations=get_theme_mod('nav_menu_locations',[]);
    $menu=wp_get_nav_menu_object('Primary');
    $menu_id=$menu?(int)$menu->term_id:wp_create_nav_menu('Primary');
    if(!is_wp_error($menu_id)){
        $items=wp_get_nav_menu_items($menu_id)?:[];
        if(!$items){
            wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Accueil','menu-item-url'=>home_url('/'),'menu-item-status'=>'publish','menu-item-type'=>'custom']);
            wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Circuits','menu-item-url'=>get_post_type_archive_link('slt_tour')?:home_url('/tours/'),'menu-item-status'=>'publish','menu-item-type'=>'custom']);
            if(!is_wp_error($contact_id)&&$contact_id)wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>'Contact','menu-item-object-id'=>(int)$contact_id,'menu-item-object'=>'page','menu-item-status'=>'publish','menu-item-type'=>'post_type']);
        } else {
            foreach($items as $item){
                $map=['Home'=>'Accueil','Tours'=>'Circuits'];
                if(isset($map[$item->title]))wp_update_nav_menu_item($menu_id,$item->ID,['menu-item-title'=>$map[$item->title]]);
            }
        }
        $locations['primary']=$menu_id;
        $locations['footer']=$menu_id;
        set_theme_mod('nav_menu_locations',$locations);
    }

    if(!get_option('slt_settings'))update_option('slt_settings',['currency'=>'EUR','deposit_percent'=>30,'payment_mode'=>'enquiry']);
    flush_rewrite_rules(false);
    update_option('slt_bootstrap_completed_v2',1,false);
    update_option('slt_bootstrap_completed_v3',1,false);
    update_option('slt_bootstrap_completed_v4',1,false);
    delete_option('slt_bootstrap_error');
},50);


/**
 * V5: informational pages and navigation only.
 * Kept separate from the original demo bootstrap so this migration can be
 * deployed/rolled back independently of tour and booking logic.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v5_pages')) return;

    $upsert_page = function (string $slug, string $title, string $content): int {
        $page = get_page_by_path($slug);
        $data = [
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => $title,
            'post_name' => $slug,
            'post_content' => $content,
        ];
        if ($page) $data['ID'] = $page->ID;
        $id = $page ? wp_update_post($data, true) : wp_insert_post($data, true);
        return is_wp_error($id) ? 0 : (int) $id;
    };

    $pages = [];
    $pages['about'] = $upsert_page('a-propos', 'À propos',
        '<p>Nous créons des voyages privés au Sri Lanka avec une approche simple : des itinéraires clairs, flexibles et pensés pour être faciles à organiser depuis l’Europe.</p>' .
        '<h2>Notre approche</h2><p>Nos circuits associent patrimoine, nature, montagnes et côte sans surcharger les journées. Les itinéraires présentés servent de base et peuvent évoluer selon les disponibilités et les préférences des voyageurs.</p>' .
        '<h2>Une organisation locale</h2><p>Les prestations sur place sont coordonnées avec des partenaires locaux au Sri Lanka afin de construire un voyage cohérent de l’arrivée au départ.</p>'
    );

    $pages['why'] = $upsert_page('pourquoi-nous', 'Pourquoi nous',
        '<h2>Voyages privés</h2><p>Vous voyagez à votre rythme, sans groupe imposé.</p>' .
        '<h2>Itinéraires lisibles</h2><p>Chaque journée présente les étapes importantes, les hôtels et les repas prévus.</p>' .
        '<h2>Flexibilité</h2><p>Les hôtels, certaines activités et le rythme peuvent être adaptés avant la confirmation finale.</p>' .
        '<h2>Paiement européen</h2><p>Le futur checkout sera conçu autour de moyens de paiement familiers en Europe, avec Mollie comme prestataire de paiement.</p>'
    );

    $pages['faq'] = $upsert_page('faq', 'Questions fréquentes',
        '<h2>Les circuits sont-ils privés ?</h2><p>Oui. Les itinéraires sont conçus pour des voyages privés.</p>' .
        '<h2>Puis-je modifier un circuit ?</h2><p>Oui. Les hôtels, étapes et activités peuvent être adaptés selon les disponibilités.</p>' .
        '<h2>Les vols sont-ils inclus ?</h2><p>Sauf indication contraire, les vols internationaux ne sont pas inclus.</p>' .
        '<h2>Comment se passe le paiement ?</h2><p>Le site est actuellement en démonstration. Un parcours de paiement européen sera ajouté progressivement après validation de l’expérience de réservation.</p>'
    );

    $pages['payment'] = $upsert_page('paiement', 'Paiement sécurisé',
        '<p>Le site sera préparé pour un paiement sécurisé via Mollie, avec des moyens de paiement adaptés aux voyageurs européens.</p>' .
        '<h2>Moyens de paiement prévus</h2><p>Wero lorsqu’il est disponible, carte bancaire, Visa, Mastercard, Apple Pay, iDEAL pour les clients néerlandais, PayPal et virement SEPA.</p>' .
        '<p><strong>Mode démonstration :</strong> aucun paiement réel n’est actuellement débité sur ce site.</p>'
    );

    $pages['terms'] = $upsert_page('conditions-generales', 'Conditions générales',
        '<p><strong>Brouillon de démonstration.</strong> Cette page devra être complétée avec les informations légales de l’entreprise, les règles d’annulation, les remboursements et les responsabilités avant toute vente réelle.</p>' .
        '<h2>Réservation</h2><p>Une réservation n’est définitive qu’après confirmation des disponibilités et acceptation du prix final.</p>' .
        '<h2>Prix et acompte</h2><p>Le montant de l’acompte et le solde restant seront indiqués avant le paiement.</p>' .
        '<h2>Modification et annulation</h2><p>Les conditions détaillées seront définies avant l’ouverture commerciale du site.</p>'
    );

    $pages['privacy'] = $upsert_page('politique-confidentialite', 'Politique de confidentialité',
        '<p><strong>Brouillon de démonstration.</strong> L’identité complète du responsable du traitement et les durées de conservation seront ajoutées avant lancement.</p>' .
        '<h2>Données collectées</h2><p>Les demandes de voyage peuvent inclure le nom, l’adresse e-mail, le téléphone, les dates et le nombre de voyageurs.</p>' .
        '<h2>Utilisation</h2><p>Ces informations servent uniquement à répondre aux demandes et organiser les prestations demandées.</p>' .
        '<h2>Paiement</h2><p>Lorsque le paiement sera activé, les données bancaires sensibles seront traitées par le prestataire de paiement et ne seront pas stockées directement sur ce site.</p>'
    );

    $contact = get_page_by_path('contact');
    $contact_id = $contact ? (int) $contact->ID : 0;

    $menu = wp_get_nav_menu_object('Primary');
    $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu('Primary');
    if (!is_wp_error($menu_id)) {
        foreach (wp_get_nav_menu_items($menu_id) ?: [] as $item) {
            wp_delete_post($item->ID, true);
        }

        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Accueil',
            'menu-item-url' => home_url('/'),
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
        ]);
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Circuits',
            'menu-item-url' => get_post_type_archive_link('slt_tour') ?: home_url('/tours/'),
            'menu-item-status' => 'publish',
            'menu-item-type' => 'custom',
        ]);

        foreach ([
            ['title' => 'À propos', 'id' => $pages['about']],
            ['title' => 'Pourquoi nous', 'id' => $pages['why']],
            ['title' => 'FAQ', 'id' => $pages['faq']],
            ['title' => 'Contact', 'id' => $contact_id],
        ] as $entry) {
            if (!$entry['id']) continue;
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $entry['title'],
                'menu-item-object-id' => $entry['id'],
                'menu-item-object' => 'page',
                'menu-item-status' => 'publish',
                'menu-item-type' => 'post_type',
            ]);
        }

        $footer = wp_get_nav_menu_object('Footer');
        $footer_id = $footer ? (int) $footer->term_id : wp_create_nav_menu('Footer');
        if (!is_wp_error($footer_id)) {
            foreach (wp_get_nav_menu_items($footer_id) ?: [] as $item) {
                wp_delete_post($item->ID, true);
            }
            foreach ([
                ['title' => 'À propos', 'id' => $pages['about']],
                ['title' => 'FAQ', 'id' => $pages['faq']],
                ['title' => 'Paiement', 'id' => $pages['payment']],
                ['title' => 'Conditions générales', 'id' => $pages['terms']],
                ['title' => 'Confidentialité', 'id' => $pages['privacy']],
                ['title' => 'Contact', 'id' => $contact_id],
            ] as $entry) {
                if (!$entry['id']) continue;
                wp_update_nav_menu_item($footer_id, 0, [
                    'menu-item-title' => $entry['title'],
                    'menu-item-object-id' => $entry['id'],
                    'menu-item-object' => 'page',
                    'menu-item-status' => 'publish',
                    'menu-item-type' => 'post_type',
                ]);
            }
        }

        $locations = get_theme_mod('nav_menu_locations', []);
        $locations['primary'] = $menu_id;
        if (!empty($footer_id) && !is_wp_error($footer_id)) $locations['footer'] = $footer_id;
        set_theme_mod('nav_menu_locations', $locations);
    }

    update_option('slt_bootstrap_completed_v5_pages', 1, false);
}, 60);


/**
 * V6: demo branding only.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v6_brand')) return;
    if (get_option('blogname') === 'My Blog' || trim((string)get_option('blogname')) === '') {
        update_option('blogname', 'Sri Lanka Voyages');
    }
    update_option('blogdescription', 'Voyages privés au Sri Lanka');
    update_option('slt_bootstrap_completed_v6_brand', 1, false);
}, 70);


/**
 * V7: complete public site structure and copy.
 */
add_action('init', function (): void {
    if (get_option('slt_bootstrap_completed_v7_complete_site')) return;

    $upsert = function(string $slug,string $title,string $content): int {
        $page=get_page_by_path($slug);
        $data=['post_type'=>'page','post_status'=>'publish','post_title'=>$title,'post_name'=>$slug,'post_content'=>$content];
        if($page)$data['ID']=$page->ID;
        $id=$page?wp_update_post($data,true):wp_insert_post($data,true);
        return is_wp_error($id)?0:(int)$id;
    };

    $pages=[];
    $pages['services']=$upsert('nos-services','Nos services',
        '<p class="lead">Un voyage au Sri Lanka ne se résume pas à une liste d’hôtels. Nous organisons les éléments essentiels autour d’un itinéraire cohérent et adaptable.</p>'.
        '<h2>Circuits privés</h2><p>Des voyages conçus pour votre groupe uniquement, avec chauffeur et étapes organisées selon l’itinéraire confirmé.</p>'.
        '<h2>Voyages sur mesure</h2><p>Vous pouvez partir d’un circuit existant ou nous transmettre vos dates, votre rythme et vos centres d’intérêt afin d’adapter le programme.</p>'.
        '<h2>Transferts & chauffeur</h2><p>Accueil à l’aéroport et déplacements privés au Sri Lanka coordonnés avec des partenaires locaux sélectionnés.</p>'.
        '<h2>Hôtels & hébergements</h2><p>Nous intégrons les hôtels au programme en fonction de la catégorie souhaitée, du parcours et des disponibilités.</p>'.
        '<h2>Excursions & expériences</h2><p>Sites culturels, safaris, plantations de thé, balades en bateau, plages et autres expériences peuvent être ajoutés à votre voyage.</p>'
    );
    $pages['how']=$upsert('comment-ca-marche','Comment ça marche',
        '<p class="lead">Une réservation claire, en quatre étapes.</p>'.
        '<h2>1. Choisissez ou imaginez votre voyage</h2><p>Commencez avec l’un de nos circuits ou décrivez-nous votre projet.</p>'.
        '<h2>2. Personnalisation & disponibilités</h2><p>Nous adaptons le rythme, les hôtels et les expériences puis vérifions les disponibilités auprès de nos partenaires.</p>'.
        '<h2>3. Confirmation du programme et du prix</h2><p>Vous recevez les prestations prévues, le prix final et les conditions applicables avant tout paiement.</p>'.
        '<h2>4. Réservation & préparation</h2><p>Après confirmation, vous réservez selon les modalités indiquées et recevez les informations utiles pour préparer votre départ.</p>'
    );
    $pages['about']=$upsert('a-propos','À propos',
        '<p class="lead">Sri Lanka Voyages est né d’une idée simple : apporter au voyage au Sri Lanka le même niveau d’attention, de communication et de fiabilité que celui attendu par les voyageurs en Europe.</p>'.
        '<h2>Une expérience avec les voyageurs internationaux</h2><p>Notre équipe s’appuie sur une expérience de service aux voyageurs en France, notamment à travers Private Cab Transfert, qui propose des transferts privés, des prises en charge aéroport et des excursions. Cette expérience nous a appris l’importance de la ponctualité, d’une communication claire et d’un accompagnement simple.</p>'.
        '<h2>Une organisation locale au Sri Lanka</h2><p>Pour les circuits au Sri Lanka, les prestations sont organisées avec des partenaires locaux. L’objectif est de réunir transport, hôtels et expériences dans un itinéraire facile à comprendre avant votre départ.</p>'.
        '<h2>Des voyages qui restent personnels</h2><p>Les programmes présentés sur le site sont des bases. Nous pouvons ajuster les étapes, la durée, les hébergements et certaines activités selon vos préférences et les disponibilités.</p>'
    );
    $pages['why']=$upsert('pourquoi-nous','Pourquoi nous choisir',
        '<h2>Voyages privés</h2><p>Votre circuit est organisé pour votre groupe, sans départ collectif imposé.</p>'.
        '<h2>Expérience du service en Europe</h2><p>Notre expérience auprès de voyageurs internationaux en France influence notre façon de communiquer et d’organiser chaque étape.</p>'.
        '<h2>Partenaires locaux</h2><p>Les prestations au Sri Lanka sont coordonnées avec des partenaires locaux afin d’assurer la continuité du voyage.</p>'.
        '<h2>Flexibilité</h2><p>Les itinéraires peuvent être ajustés avant confirmation selon vos dates, votre rythme et vos préférences.</p>'.
        '<h2>Clarté avant paiement</h2><p>Le programme, les prestations et le prix final sont confirmés avant la réservation définitive.</p>'
    );
    $pages['faq']=$upsert('faq','Questions fréquentes',
        '<h2>Les circuits sont-ils privés ?</h2><p>Oui. Les itinéraires sont prévus pour votre groupe uniquement.</p>'.
        '<h2>Puis-je modifier un circuit ?</h2><p>Oui. La durée, certains hôtels, étapes et activités peuvent être adaptés selon les disponibilités.</p>'.
        '<h2>Les vols internationaux sont-ils inclus ?</h2><p>Non, sauf mention explicite dans une proposition personnalisée.</p>'.
        '<h2>Comment le prix est-il calculé ?</h2><p>Il dépend notamment des dates, du nombre de voyageurs, des hôtels, du transport et des activités choisies.</p>'.
        '<h2>Comment se passe le paiement ?</h2><p>Le programme et le prix sont d’abord confirmés. Le site est actuellement en démonstration ; le paiement en ligne sera activé avec un prestataire européen avant l’ouverture commerciale.</p>'.
        '<h2>Qui organise le voyage sur place ?</h2><p>Les prestations au Sri Lanka sont exécutées et coordonnées avec des partenaires locaux selon le programme confirmé.</p>'
    );

    $contact=get_page_by_path('contact');
    $contact_id=$contact?(int)$contact->ID:0;

    $menu=wp_get_nav_menu_object('Primary');
    $menu_id=$menu?(int)$menu->term_id:wp_create_nav_menu('Primary');
    if(!is_wp_error($menu_id)){
        foreach(wp_get_nav_menu_items($menu_id)?:[] as $item)wp_delete_post($item->ID,true);
        $entries=[
            ['Accueil',0,home_url('/')],
            ['Circuits',0,get_post_type_archive_link('slt_tour')?:home_url('/tours/')],
            ['Nos services',$pages['services'],''],
            ['À propos',$pages['about'],''],
            ['FAQ',$pages['faq'],''],
            ['Contact',$contact_id,''],
        ];
        foreach($entries as [$title,$id,$url]){
            if($id)wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$title,'menu-item-object-id'=>$id,'menu-item-object'=>'page','menu-item-status'=>'publish','menu-item-type'=>'post_type']);
            elseif($url)wp_update_nav_menu_item($menu_id,0,['menu-item-title'=>$title,'menu-item-url'=>$url,'menu-item-status'=>'publish','menu-item-type'=>'custom']);
        }
        $locations=get_theme_mod('nav_menu_locations',[]);
        $locations['primary']=$menu_id;
        set_theme_mod('nav_menu_locations',$locations);
    }

    update_option('blogname','Sri Lanka Voyages');
    update_option('blogdescription','Voyages privés et sur mesure au Sri Lanka');
    update_option('slt_bootstrap_completed_v7_complete_site',1,false);
    flush_rewrite_rules(false);
},80);
